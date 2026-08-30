<?php

namespace App\Security\Voter;

use App\Entity\SousTache;

final class SousTacheVoter extends AbstractResponsableVoter
{
    protected function supportedClass(): string
    {
        return SousTache::class;
    }

    protected function responsableEmails(object $subject): array
    {
        /** @var SousTache $subject */
        return [
            $subject->getResponsableEmail(),
            $subject->getTache()?->getResponsableEmail(),
            $subject->getTache()?->getAction()?->getResponsableEmail(),
            $subject->getTache()?->getAction()?->getProgramme()?->getRprogEmail(),
        ];
    }
}
