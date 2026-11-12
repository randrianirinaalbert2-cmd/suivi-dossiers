<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceRequise extends ModeleBase
{
    protected $table = 'pieces_requises';

    protected $fillable = ['type_dossier_id', 'type_piece_id', 'nb_exemplaires'];

    protected function casts(): array
    {
        return [
            'nb_exemplaires' => 'integer',
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function typeDossier(): BelongsTo
    {
        return $this->belongsTo(TypeDossier::class);
    }

    public function typePiece(): BelongsTo
    {
        return $this->belongsTo(TypePiece::class);
    }
}
