<?php

namespace App\Enums;

enum MotifRejet: string
{
    case PiecesManquantes = 'pieces_manquantes';
    case PieceIncomplete = 'piece_incomplete';
    case Autre = 'autre';

    /**
     * Libellé français du motif, affiché dans la liste déroulante.
     */
    public function label(): string
    {
        return match ($this) {
            self::PiecesManquantes => 'Pièces manquantes',
            self::PieceIncomplete => 'Pièce incomplète ou non conforme',
            self::Autre => 'Autre motif (à préciser)',
        };
    }

    /**
     * Aide affichée sous la liste pour guider la saisie de la précision.
     */
    public function aide(): string
    {
        return match ($this) {
            self::PiecesManquantes => 'Les pièces absentes sont listées automatiquement ci-dessous.',
            self::PieceIncomplete => 'Exemple : numéro de compte incomplet, document illisible, attestation erronée…',
            self::Autre => 'Précisez le motif : la précision est obligatoire.',
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
