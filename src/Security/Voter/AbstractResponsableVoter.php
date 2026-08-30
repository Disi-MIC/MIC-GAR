<?php

namespace App\Security\Voter;

use App\Security\JwtRemoteUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Base commune aux Voters GAR : GERM ne connaît pas la notion de "responsable
 * de programme/action/tâche" (spécifique à MIC-GAR), donc pas de rôle dédié
 * dans le JWT — l'autorisation d'édition se calcule ici par comparaison
 * d'email plutôt que par un rôle statique, voir Partie "Décisions
 * d'architecture" du plan MIC-GAR. Un porteur de ROLE_AUTORITE ou
 * ROLE_SUPERADMIN (rôles GERM déjà résolus dans le JWT) a un accès complet,
 * cohérent avec leur portée ministérielle dans GERM.
 */
abstract class AbstractResponsableVoter extends Voter
{
    public const EDIT = 'GAR_EDIT';

    private const ROLES_AUTORITE_GAR = ['ROLE_AUTORITE', 'ROLE_SUPERADMIN'];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EDIT === $attribute && is_a($subject, $this->supportedClass());
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof JwtRemoteUser) {
            return false;
        }

        if ([] !== array_intersect(self::ROLES_AUTORITE_GAR, $user->getRoles())) {
            return true;
        }

        $emailsAutorises = array_map(strtolower(...), array_filter($this->responsableEmails($subject)));

        return in_array(strtolower($user->getEmail()), $emailsAutorises, true);
    }

    abstract protected function supportedClass(): string;

    /**
     * Emails autorisés à éditer $subject : son propre responsable, plus ceux
     * des niveaux GAR parents (un RProg supervise toute sa chaîne
     * Programme > Action > Tâche > Sous-tâche).
     *
     * @return string[]
     */
    abstract protected function responsableEmails(object $subject): array;
}
