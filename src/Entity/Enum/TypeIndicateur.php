<?php

namespace App\Entity\Enum;

/**
 * Position dans la chaîne de résultats GAR : un extrant est un livrable
 * direct d'une Action, un effet mesure l'impact du Programme sur ses
 * bénéficiaires — distinction structurante du cadre logique GAR.
 */
enum TypeIndicateur: string
{
    case EFFET = 'effet';
    case EXTRANT = 'extrant';

    public function label(): string
    {
        return match ($this) {
            self::EFFET => "Indicateur d'effet",
            self::EXTRANT => "Indicateur d'extrant",
        };
    }
}
