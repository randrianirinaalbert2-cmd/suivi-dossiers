<?php

namespace App\Enums;

enum FamilleMouvement: string
{
    case Creation = 'creation';
    case ModificationSituation = 'modification_situation';
    case PositionsDiverses = 'positions_diverses';
    case Sanctions = 'sanctions';
    case Annulation = 'annulation';
    case Regularisation = 'regularisation';
    case Exclusion = 'exclusion';

    /**
     * Numéro romain de la famille dans la liste des codes mouvements.
     */
    public function numero(): string
    {
        return match ($this) {
            self::Creation => 'I',
            self::ModificationSituation => 'II',
            self::PositionsDiverses => 'III',
            self::Sanctions => 'IV',
            self::Annulation => 'V',
            self::Regularisation => 'VI',
            self::Exclusion => 'VII',
        };
    }

    /**
     * Libellé français de la famille.
     */
    public function label(): string
    {
        return match ($this) {
            self::Creation => 'Création',
            self::ModificationSituation => 'Modification de situation',
            self::PositionsDiverses => 'Positions diverses',
            self::Sanctions => 'Sanctions',
            self::Annulation => 'Annulation',
            self::Regularisation => 'Régularisation / modification de situation',
            self::Exclusion => 'Exclusion',
        };
    }

    public function libelleComplet(): string
    {
        return $this->numero().' — '.$this->label();
    }

    /**
     * Classes Tailwind du badge.
     */
    public function couleur(): string
    {
        return match ($this) {
            self::Creation => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::ModificationSituation => 'bg-sky-100 text-sky-800 ring-sky-200',
            self::PositionsDiverses => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
            self::Sanctions => 'bg-rose-100 text-rose-800 ring-rose-200',
            self::Annulation => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::Regularisation => 'bg-violet-100 text-violet-800 ring-violet-200',
            self::Exclusion => 'bg-slate-200 text-slate-800 ring-slate-300',
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
