<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BordereauRecu extends ModeleBase
{
    protected $table = 'bordereaux_recus';

    protected $fillable = [
        'division_id',
        'service_origine_id',
        'lieu_id',
        'utilisateur_id',
        'reference',
        'objet',
        'date_bordereau',
        'num_arrivee',
        'date_arrivee',
        'observation',
        'nombre_dossiers_attendus',
    ];

    protected function casts(): array
    {
        return [
            'date_bordereau' => 'date',
            'date_arrivee' => 'date',
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function serviceOrigine(): BelongsTo
    {
        return $this->belongsTo(ServiceOrigine::class);
    }

    public function lieu(): BelongsTo
    {
        return $this->belongsTo(Lieu::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentNumerise::class);
    }

    public function libelle(): string
    {
        return "N° {$this->num_arrivee} / {$this->reference}";
    }

    /**
     * Nombre de dossiers réellement rattachés à ce bordereau.
     */
    public function nombreDossiersSaisis(): int
    {
        return $this->dossiers()->count();
    }

    /**
     * Le nombre saisi correspond-il au nombre porté sur le bordereau papier ?
     */
    public function nombreDossiersConforme(): bool
    {
        return $this->nombre_dossiers_attendus === null
            || (int) $this->nombre_dossiers_attendus === $this->nombreDossiersSaisis();
    }
}
