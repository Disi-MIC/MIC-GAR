<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Identité de l'agent connecté, entièrement dérivée des claims du JWT SSO
 * émis par GERM — MIC-GAR n'a pas de table `user` locale, donc pas de
 * PasswordAuthenticatedUserInterface : il n'y a rien à ré-authentifier
 * localement, l'authenticator (JwtAuthenticator) fait toute la vérification.
 */
final class JwtRemoteUser implements UserInterface
{
    /**
     * @param string[] $roles rôles d'autorité déjà résolus côté GERM
     *                        (hiérarchie de rôles appliquée avant émission du
     *                        JWT) — voir SsoTokenService::issue() dans GERM.
     */
    public function __construct(
        private readonly string $id,
        private readonly string $email,
        private readonly ?string $nom,
        private readonly ?string $prenom,
        private readonly array $roles,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
