<?php

namespace App\Models;

use App\Enums\StatutDossier;
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
            'situation_confirmee' => 'Situation de l\'agent confirmée',
            default => ucfirst(str_replace('_', ' ', $this->type_action)),
        };
    }

    /**
     * Toutes les informations de traçabilité affichées par le bouton
     * « Voir les détails » de l'historique d'un dossier.
     *
     * @return array<string, string|null>
     */
    public function detailsTracabilite(): array
    {
        $utilisateur = $this->utilisateur;

        return [
            'identifiant' => (string) $this->id,
            'action' => $this->libelleAction(),
            'type_action' => $this->type_action,
            'date_heure' => $this->date_heure_action?->format('d/m/Y à H:i:s'),
            'agent' => $utilisateur?->nom ?: 'Utilisateur supprimé',
            'courriel' => $utilisateur?->courriel ?: '—',
            'role' => $utilisateur?->role?->label() ?: '—',
            'division' => $utilisateur?->division?->nom ?: '—',
            'statut_avant' => $this->ancien_statut ? StatutDossier::libelle($this->ancien_statut) : null,
            'statut_apres' => $this->nouveau_statut ? StatutDossier::libelle($this->nouveau_statut) : null,
            'observation' => $this->observation ?: 'Aucune observation enregistrée.',
        ];
    }
}
