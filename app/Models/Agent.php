<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends ModeleBase
{
    protected $table = 'agents';

    protected $fillable = ['im', 'nom', 'prenoms'];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function lignesBordereau(): HasMany
    {
        return $this->hasMany(LigneBordereauCreation::class);
    }

    public function nomComplet(): string
    {
        return trim("{$this->nom} {$this->prenoms}");
    }

    public function libelle(): string
    {
        return "{$this->im} — {$this->nomComplet()}";
    }
}
