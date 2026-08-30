<?php

namespace App\Security\Voter;

use App\Entity\Action;

final class ActionVoter extends AbstractResponsableVoter
{
    protected function supportedClass(): string
    {
        return Action::class;
    }

    protected function responsableEmails(object $subject): array
    {
        /** @var Action $subject */
        return [
            $subject->getResponsableEmail(),
            $subject->getProgramme()?->getRprogEmail(),
        ];
    }
}
