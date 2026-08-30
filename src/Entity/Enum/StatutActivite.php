<?php

namespace App\Entity\Enum;

/**
 * Statut de suivi commun à Programme/Action/Tâche/Sous-tâche — vocabulaire
 * standard de suivi de PTA (Plan de Travail Annuel).
 */
enum StatutActivite: string
{
    case PLANIFIE = 'planifie';
    case EN_COURS = 'en_cours';
    case REALISE = 'realise';
    case RETARDE = 'retarde';
    case ANNULE = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::PLANIFIE => 'Planifié',
            self::EN_COURS => 'En cours',
            self::REALISE => 'Réalisé',
            self::RETARDE => 'Retardé',
            self::ANNULE => 'Annulé',
        };
    }
}
