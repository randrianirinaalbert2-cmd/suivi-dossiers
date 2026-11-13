<?php

namespace App\Models;

use App\Enums\PoleDossier;
use App\Enums\StatutDossier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dossier extends ModeleBase
{
    protected $table = 'dossiers';

    protected $fillable = [
        'bordereau_recu_id',
        'division_id',
        'agent_id',
        'type_dossier_id',
        'responsable_id',
        'statut',
        'mois_exploitation',
        'pole',
        'passe_par_visa',
        'reference_acte',
        'date_acte',
        'num_visa',
        'date_visa',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutDossier::class,
            'pole' => PoleDossier::class,
            'passe_par_visa' => 'boolean',
            'date_visa' => 'date',
            'date_acte' => 'date',
            'cree_le' => 'datetime',
            'modifie_le' => 'datetime',
        ];
    }

    public function estAuVisa(): bool
    {
        return $this->pole === PoleDossier::Visa;
    }

    /**
     * Le mois d'exploitation ne concerne que le pôle Solde.
     *
     * Un dossier reçu par la division Visa n'a pas de mois d'exploitation :
     * il ne l'acquiert qu'une fois transmis au pôle Solde. Le mois d'arrivée,
     * lui, reste disponible pour toutes les divisions.
     */
    public function utiliseMoisExploitation(): bool
    {
        return ! $this->estAuVisa();
    }

    /**
     * Statuts à partir desquels un dossier peut figurer sur un bordereau
     * d'envoi (BE) : le dossier doit être traité, ou au-delà.
     *
     * @return list<string>
     */
    public static function statutsPourBordereauEnvoi(): array
    {
        return [
            StatutDossier::Traite->value,
            StatutDossier::Transmis->value,
            StatutDossier::Archive->value,
        ];
    }

    public function bordereauRecu(): BelongsTo
    {
        return $this->belongsTo(BordereauRecu::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function typeDossier(): BelongsTo
    {
        return $this->belongsTo(TypeDossier::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function piecesRecues(): HasMany
    {
        return $this->hasMany(PieceRecue::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentNumerise::class);
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(Historique::class)->orderByDesc('date_heure_action');
    }

    /**
     * Pièces exigées par le type de dossier du dossier courant.
     *
     * @return Builder<PieceRequise>
     */
    public function piecesRequises(): Builder
    {
        return PieceRequise::where('type_dossier_id', $this->type_dossier_id);
    }

    /**
     * Avancement du dossier : pièces reçues / pièces exigées.
     *
     * @return array{recues: int, exigees: int, manquantes: int, taux: float}
     */
    public function completude(): array
    {
        $exigees = $this->piecesRequises()->count();
        $recues = $this->piecesRecues()->whereIn('type_piece_id', $this->piecesRequises()->pluck('type_piece_id'))->count();

        return [
            'recues' => $recues,
            'exigees' => $exigees,
            'manquantes' => max(0, $exigees - $recues),
            'taux' => $exigees > 0 ? round(($recues / $exigees) * 100) : 100,
        ];
    }

    public function estComplet(): bool
    {
        return $this->completude()['manquantes'] === 0;
    }
}
