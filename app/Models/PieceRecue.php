<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceRecue extends ModeleBase
{
    protected $table = 'pieces_recues';

    protected $fillable = ['dossier_id', 'type_piece_id'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function typePiece(): BelongsTo
    {
        return $this->belongsTo(TypePiece::class);
    }
}
