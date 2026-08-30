<?php

namespace App\Entity\Enum;

enum Periodicite: string
{
    case ANNUELLE = 'annuelle';
    case SEMESTRIELLE = 'semestrielle';
    case TRIMESTRIELLE = 'trimestrielle';

    public function label(): string
    {
        return match ($this) {
            self::ANNUELLE => 'Annuelle',
            self::SEMESTRIELLE => 'Semestrielle',
            self::TRIMESTRIELLE => 'Trimestrielle',
        };
    }
}
