<?php

namespace App\Security\Voter;

use App\Entity\Tache;

final class TacheVoter extends AbstractResponsableVoter
{
    protected function supportedClass(): string
    {
        return Tache::class;
    }

    protected function responsableEmails(object $subject): array
    {
        /** @var Tache $subject */
        return [
            $subject->getResponsableEmail(),
            $subject->getAction()?->getResponsableEmail(),
            $subject->getAction()?->getProgramme()?->getRprogEmail(),
        ];
    }
}
