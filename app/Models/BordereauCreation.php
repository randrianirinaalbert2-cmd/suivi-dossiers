<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BordereauCreation extends ModeleBase
{
    protected $table = 'bordereaux_creation';

    protected $fillable = [
        'division_id',
        'lieu_id',
        'destination_id',
        'utilisateur_id',
        'chapitre',
        'paquet',
        'annee',
        'num_ordre',
        'date_edition',
        'observation',
        'statut',
        'archive_le',
        'archive_par_id',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'num_ordre' => 'integer',
            'date_edition' => 'date',
            'archive_le' => 'datetime',
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function lieu(): BelongsTo
    {
        return $this->belongsTo(Lieu::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneBordereauCreation::class);
    }

    public function archivePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archive_par_id');
    }

    public function estArchive(): bool
    {
        return $this->statut === 'archive';
    }

    public function scopeArchives($query)
    {
        return $query->where('statut', 'archive');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', '!=', 'archive');
    }

    /**
     * Prochain numéro d'ordre de l'année courante.
     */
    public static function prochainNumero(int $annee): int
    {
        return (int) static::where('annee', $annee)->max('num_ordre') + 1;
    }

    public function reference(): string
    {
        return sprintf('%s/%s/%s/%s/%04d', $this->chapitre, $this->paquet ?: '-', $this->annee, $this->lieu?->sigle, $this->num_ordre);
    }
}
