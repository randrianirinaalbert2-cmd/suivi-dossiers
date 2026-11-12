<?php

namespace App\Enums;

enum PoleDossier: string
{
    case Solde = 'solde';
    case Visa = 'visa';

    /**
     * Libellé français du pôle.
     */
    public function label(): string
    {
        return match ($this) {
            self::Solde => 'Solde',
            self::Visa => 'Visa',
        };
    }

    /**
     * Description du pôle dans le circuit de traitement.
     */
    public function description(): string
    {
        return match ($this) {
            self::Solde => 'Dossier pris en charge par le pôle solde.',
            self::Visa => 'Dossier en attente de visa avant retour au solde.',
        };
    }

    /**
     * Classes Tailwind du badge.
     */
    public function couleur(): string
    {
        return match ($this) {
            self::Solde => 'bg-sky-100 text-sky-800 ring-sky-200',
            self::Visa => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function valeurs(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function libelle(string $valeur): string
    {
        return self::tryFrom($valeur)?->label() ?? $valeur;
    }
}
