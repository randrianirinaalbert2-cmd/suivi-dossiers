<?php

namespace App\Models;

use App\Casts\FamilleMouvementCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDossier extends ModeleBase
{
    protected $table = 'types_dossiers';

    protected $fillable = ['division_id', 'code', 'libelle', 'famille'];

    protected function casts(): array
    {
        return [
            'famille' => FamilleMouvementCast::class,
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * @return HasMany<PieceRequise, $this>
     */
    public function piecesRequises(): HasMany
    {
        return $this->hasMany(PieceRequise::class);
    }

    public function typesPieces(): BelongsToMany
    {
        return $this->belongsToMany(TypePiece::class, 'pieces_requises', 'type_dossier_id', 'type_piece_id')
            ->withPivot('nb_exemplaires')
            ->orderBy('types_pieces.libelle');
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function lignesBordereau(): HasMany
    {
        return $this->hasMany(LigneBordereauCreation::class);
    }

    public function libelleComplet(): string
    {
        return "[{$this->code}] {$this->libelle}";
    }
}
