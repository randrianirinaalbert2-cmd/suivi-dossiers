<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneBordereauCreation extends ModeleBase
{
    protected $table = 'lignes_bordereau_creation';

    protected $fillable = [
        'bordereau_creation_id',
        'agent_id',
        'agent_im',
        'agent_nom',
        'agent_prenoms',
        'type_dossier_id',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function bordereauCreation(): BelongsTo
    {
        return $this->belongsTo(BordereauCreation::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function typeDossier(): BelongsTo
    {
        return $this->belongsTo(TypeDossier::class);
    }

    public function imAgent(): ?string
    {
        return $this->agent_im ?: $this->agent?->im;
    }

    public function nomAgent(): ?string
    {
        return $this->agent_nom ?: $this->agent?->nom;
    }

    public function prenomsAgent(): ?string
    {
        return $this->agent_prenoms ?: $this->agent?->prenoms;
    }

    public function nomCompletAgent(): string
    {
        return trim($this->nomAgent().' '.$this->prenomsAgent());
    }
}
