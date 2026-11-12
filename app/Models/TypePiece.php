<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypePiece extends ModeleBase
{
    protected $table = 'types_pieces';

    protected $fillable = ['libelle'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function piecesRequises(): HasMany
    {
        return $this->hasMany(PieceRequise::class);
    }

    public function piecesRecues(): HasMany
    {
        return $this->hasMany(PieceRecue::class);
    }

    public function typesDossiers(): BelongsToMany
    {
        return $this->belongsToMany(TypeDossier::class, 'pieces_requises', 'type_piece_id', 'type_dossier_id')
            ->withPivot('nb_exemplaires');
    }
}
