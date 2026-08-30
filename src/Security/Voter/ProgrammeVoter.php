<?php

namespace App\Security\Voter;

use App\Entity\Programme;

final class ProgrammeVoter extends AbstractResponsableVoter
{
    protected function supportedClass(): string
    {
        return Programme::class;
    }

    protected function responsableEmails(object $subject): array
    {
        /** @var Programme $subject */
        return [$subject->getRprogEmail()];
    }
}
