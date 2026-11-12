<?php

namespace App\Enums;

enum StatutDossier: string
{
    case Recu = 'recu';
    case EnTraitement = 'en_traitement';
    case Traite = 'traite';
    case Verifie = 'verifie';
    case Transmis = 'transmis';
    case Archive = 'archive';
    case Incomplet = 'incomplet';
    case Rejete = 'rejete';

    /**
     * Libellé français du statut.
     */
    public function label(): string
    {
        return match ($this) {
            self::Recu => 'Reçu',
            self::EnTraitement => 'En traitement',
            self::Traite => 'Traité',
            self::Verifie => 'Vérifié',
            self::Transmis => 'Transmis',
            self::Archive => 'Archivé',
            self::Incomplet => 'Incomplet',
            self::Rejete => 'Rejeté',
        };
    }

    /**
     * Classes Tailwind du badge.
     */
    public function couleur(): string
    {
        return match ($this) {
            self::Recu => 'bg-slate-100 text-slate-700 ring-slate-200',
            self::EnTraitement => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::Traite => 'bg-sky-100 text-sky-800 ring-sky-200',
            self::Verifie => 'bg-indigo-100 text-indigo-800 ring-indigo-200',
            self::Transmis => 'bg-violet-100 text-violet-800 ring-violet-200',
            self::Archive => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::Incomplet => 'bg-orange-100 text-orange-800 ring-orange-200',
            self::Rejete => 'bg-rose-100 text-rose-800 ring-rose-200',
        };
    }

    /**
     * Statuts atteignables depuis ce statut (workflow du circuit de validation).
     *
     * @return array<int, self>
     */
    public function suite(): array
    {
        return match ($this) {
            self::Recu => [self::EnTraitement, self::Incomplet, self::Rejete],
            self::EnTraitement => [self::Verifie, self::Incomplet, self::Rejete],
            self::Verifie => [self::Traite, self::EnTraitement],
            self::Traite => [self::Transmis, self::Verifie],
            self::Transmis => [self::Archive],
            self::Incomplet => [self::EnTraitement],
            self::Archive, self::Rejete => [],
        };
    }

    public function estTerminal(): bool
    {
        return $this->suite() === [];
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
        $statut = self::tryFrom($valeur);

        return $statut?->label() ?? $valeur;
    }
}
