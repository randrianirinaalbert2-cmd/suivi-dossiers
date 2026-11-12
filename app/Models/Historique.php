<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Historique extends Model
{
    protected $table = 'historiques';

    public const CREATED_AT = 'date_heure_action';

    public const UPDATED_AT = null;

    protected $fillable = [
        'dossier_id',
        'utilisateur_id',
        'type_action',
        'ancien_statut',
        'nouveau_statut',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'date_heure_action' => 'datetime',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /**
     * Libellé de l'action enregistrée.
     */
    public function libelleAction(): string
    {
        return match ($this->type_action) {
            'creation' => 'Création du dossier',
            'changement_statut' => 'Changement de statut',
            'ajout_piece' => 'Pièce réceptionnée',
            'retrait_piece' => 'Pièce retirée',
            'numerisation' => 'Document numérisé',
            'modification' => 'Modification',
            'ajout_ligne' => 'Ligne ajoutée au bordereau',
            'visa' => 'Contrôle et visa du dossier',
            'prise_en_charge' => 'Prise en charge du dossier',
            'attribution_responsable' => 'Attribution / prise du dossier',
            default => ucfirst(str_replace('_', ' ', $this->type_action)),
        };
    }
}
