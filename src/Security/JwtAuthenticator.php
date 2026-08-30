<?php

namespace App\Security;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Vérifie le JWT RS256 émis par GERM à la connexion (voir SsoTokenService
 * côté GERM) — aucun appel réseau : la signature est vérifiée localement
 * avec la clé publique de GERM, et l'identité (JwtRemoteUser) est construite
 * entièrement à partir des claims, sans lookup en base (MIC-GAR n'a pas de
 * table `user`).
 */
final class JwtAuthenticator extends AbstractAuthenticator
{
    private readonly string $publicKeyPem;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
        #[Autowire(env: 'SSO_JWT_PUBLIC_KEY_PATH')]
        string $publicKeyPath,
        #[Autowire(env: 'SSO_JWT_ISSUER')]
        private readonly string $expectedIssuer,
        #[Autowire(env: 'SSO_JWT_AUDIENCE')]
        private readonly string $expectedAudience,
    ) {
        $absolutePath = str_starts_with($publicKeyPath, '/') ? $publicKeyPath : $projectDir.'/'.$publicKeyPath;
        $contents = @file_get_contents($absolutePath);

        if (false === $contents) {
            throw new \RuntimeException(\sprintf('Clé publique SSO introuvable : "%s". Voir SSO_JWT_PUBLIC_KEY_PATH et Partie 1 du plan MIC-GAR (export de config/jwt/sso_public.pem depuis GERM).', $absolutePath));
        }

        $this->publicKeyPem = $contents;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $token = substr($authorizationHeader, \strlen('Bearer '));

        if ('' === $token) {
            throw new CustomUserMessageAuthenticationException('Jeton SSO manquant.');
        }

        try {
            $claims = JWT::decode($token, new Key($this->publicKeyPem, 'RS256'));
        } catch (ExpiredException) {
            throw new CustomUserMessageAuthenticationException('Jeton SSO expiré, reconnectez-vous depuis GERM.');
        } catch (SignatureInvalidException) {
            throw new CustomUserMessageAuthenticationException('Jeton SSO invalide.');
        } catch (\Exception) {
            throw new CustomUserMessageAuthenticationException('Jeton SSO illisible.');
        }

        if (($claims->iss ?? null) !== $this->expectedIssuer || ($claims->aud ?? null) !== $this->expectedAudience) {
            throw new CustomUserMessageAuthenticationException('Jeton SSO émis pour une autre application.');
        }

        if (empty($claims->email) || empty($claims->sub)) {
            throw new CustomUserMessageAuthenticationException('Jeton SSO incomplet.');
        }

        $userIdentifier = $claims->email;
        $roles = (array) ($claims->roles ?? []);
        $id = (string) $claims->sub;
        $nom = $claims->nom ?? null;
        $prenom = $claims->prenom ?? null;

        return new SelfValidatingPassport(
            new UserBadge($userIdentifier, static fn () => new JwtRemoteUser($id, $userIdentifier, $nom, $prenom, $roles))
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['message' => 'Authentification requise (jeton SSO Bearer manquant).'], Response::HTTP_UNAUTHORIZED);
    }
}
