<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Lieu extends ModeleBase
{
    protected $table = 'lieux';

    protected $fillable = ['code', 'nom', 'sigle'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function bordereauxCreation(): HasMany
    {
        return $this->hasMany(BordereauCreation::class);
    }

    public function libelleComplet(): string
    {
        return "{$this->code} — {$this->nom} ({$this->sigle})";
    }
}
