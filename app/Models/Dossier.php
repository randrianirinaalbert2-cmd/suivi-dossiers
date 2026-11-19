<?php

namespace App\Models;

use App\Enums\PoleDossier;
use App\Enums\StatutDossier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Dossier extends ModeleBase
{
    /**
     * Types de dossier dont l'acte formaté passe de 2 à 4 exemplaires
     * lorsqu'un rappel de solde est demandé (intégration, renouvellement
     * de contrat).
     *
     * @var list<string>
     */
    public const CODES_AVEC_RAPPEL = ['10', '17'];

    /**
     * Libellé de la pièce dont le nombre d'exemplaires dépend du rappel.
     */
    public const PIECE_ACTE_FORMATE = 'Acte formaté';

    /**
     * Types dont la situation de l'agent détermine les pièces exigées.
     *
     * Pour ces CMVT, la situation doit être confirmée avant tout traitement :
     * c'est elle qui décide quelles pièces sont réellement demandées (17 :
     * interruption de service ; 60 : enfants, mariage ou célibat, CNAPS).
     *
     * @var list<string>
     */
    public const CODES_SITUATION_A_CONFIRMER = ['17', '60'];

    /**
     * Conditions de dossier pouvant rendre une pièce exigible.
     *
     * La clé est enregistrée dans pieces_requises.condition (ou
     * pieces_requises.condition_exemplaires) ; le libellé sert à expliquer
     * la règle au guichet.
     *
     * @var array<string, string>
     */
    public const CONDITIONS = [
        'interruption_contrat_plus_3_mois' => 'interruption de contrat de plus de 3 mois',
        'a_des_enfants' => 'agent ayant au moins un enfant',
        'enfant_moins_de_4_ans' => 'enfant de moins de 4 ans',
        'marie' => 'agent marié(e)',
        'celibataire' => 'agent célibataire',
        'enfant_non_integre' => 'enfant pas encore intégré (CNAPS)',
    ];

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
        'avec_rappel',
        'nombre_enfants',
        'enfant_moins_de_4_ans',
        'situation_familiale',
        'interruption_contrat_plus_3_mois',
        'enfant_deja_integre',
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
            'avec_rappel' => 'boolean',
            'nombre_enfants' => 'integer',
            'enfant_moins_de_4_ans' => 'boolean',
            'interruption_contrat_plus_3_mois' => 'boolean',
            'enfant_deja_integre' => 'boolean',
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
     * Pièces reçues pour ce dossier, avec la personne qui les a prises.
     *
     * La table pieces_recues ne conservant pas d'utilisateur, l'agent est
     * déduit des actions « ajout_piece » de l'historique.
     *
     * @return array<int, array{piece: string, agent: string, role: string, date: string}>
     */
    public function piecesRecuesAvecAgent(): array
    {
        return $this->historiques
            ->where('type_action', 'ajout_piece')
            ->sortBy('date_heure_action')
            ->map(fn (Historique $action): array => [
                'piece' => Str::after($action->observation ?: '', 'Pièce : '),
                'agent' => $action->utilisateur?->nom ?: 'Utilisateur supprimé',
                'role' => $action->utilisateur?->role?->label() ?: '—',
                'date' => $action->date_heure_action?->format('d/m/Y à H:i') ?: '—',
            ])
            ->values()
            ->all();
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
     * Le rappel ne s'applique qu'aux types prévus par la liste officielle.
     */
    public function avecRappelEffectif(): bool
    {
        return (bool) $this->avec_rappel
            && in_array($this->typeDossier?->code, self::CODES_AVEC_RAPPEL, true);
    }

    /**
     * La situation du dossier remplit-elle la condition donnée ?
     *
     * Une condition inconnue est considérée comme remplie : une pièce ne
     * disparaît jamais par accident d'un dossier à cause d'une faute de
     * frappe dans le paramétrage.
     */
    public function conditionRemplie(string $condition): bool
    {
        return match ($condition) {
            'interruption_contrat_plus_3_mois' => (bool) $this->interruption_contrat_plus_3_mois,
            'a_des_enfants' => (int) $this->nombre_enfants > 0,
            'enfant_moins_de_4_ans' => (int) $this->nombre_enfants > 0 && (bool) $this->enfant_moins_de_4_ans,
            'marie' => $this->situation_familiale === 'marie',
            'celibataire' => $this->situation_familiale === 'celibataire',
            'enfant_non_integre' => ! (bool) $this->enfant_deja_integre,
            default => true,
        };
    }

    public static function libelleCondition(?string $condition): ?string
    {
        if ($condition === null) {
            return null;
        }

        return self::CONDITIONS[$condition] ?? $condition;
    }

    /**
     * Pièces exigées pour ce dossier, conditions et rappel compris.
     *
     * Les pièces conditionnelles ne sont retenues que si la situation du
     * dossier remplit leur condition. Le nombre d'exemplaires peut être
     * remplacé lorsqu'une condition de quantité est vraie (acte formaté à
     * quatre exemplaires pour un enfant de moins de 4 ans). Le rappel des
     * types 10 et 17 ajoute enfin deux exemplaires de l'acte formaté.
     *
     * Rien de tout cela n'est enregistré en base : le résultat dépend du
     * dossier et reste recalculé à chaque affichage.
     *
     * @return Collection<int, PieceRequise>
     */
    public function piecesExigees(): Collection
    {
        $pieces = $this->piecesRequises()
            ->with('typePiece')
            ->get()
            ->filter(fn (PieceRequise $piece) => $piece->condition === null
                || $this->conditionRemplie($piece->condition))
            ->sortBy(fn (PieceRequise $piece) => $piece->typePiece?->libelle)
            ->values();

        foreach ($pieces as $piece) {
            if ($piece->condition_exemplaires !== null
                && $piece->nb_exemplaires_condition !== null
                && $this->conditionRemplie($piece->condition_exemplaires)) {
                $piece->nb_exemplaires = $piece->nb_exemplaires_condition;
            }
        }

        if (! $this->avecRappelEffectif()) {
            return $pieces;
        }

        foreach ($pieces as $piece) {
            if ($piece->typePiece?->libelle === self::PIECE_ACTE_FORMATE) {
                $piece->nb_exemplaires += 2;
            }
        }

        return $pieces;
    }

    /**
     * Avancement du dossier : pièces reçues / pièces réellement exigées.
     *
     * Seules les pièces applicables au dossier sont comptées : une pièce
     * conditionnelle dont la condition n'est pas remplie ne doit ni peser
     * sur le taux, ni bloquer la validation.
     *
     * @return array{recues: int, exigees: int, manquantes: int, taux: float}
     */
    public function completude(): array
    {
        $pieces = $this->piecesExigees();
        $exigees = $pieces->count();
        $recues = $this->piecesRecues()
            ->whereIn('type_piece_id', $pieces->pluck('type_piece_id'))
            ->count();

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

    /**
     * La situation de l'agent doit-elle être confirmée pour ce dossier ?
     */
    public function situationAConfirmer(): bool
    {
        return in_array($this->typeDossier?->code, self::CODES_SITUATION_A_CONFIRMER, true);
    }

    /**
     * Dernière confirmation de la situation enregistrée dans l'historique.
     */
    public function derniereConfirmationSituation(): ?Historique
    {
        return $this->historiques()
            ->where('type_action', 'situation_confirmee')
            ->first();
    }

    /**
     * La situation de l'agent a-t-elle déjà été confirmée ?
     *
     * Tant qu'elle ne l'est pas, la liste des pièces reste provisoire : les
     * conditions sont évaluées sur des valeurs par défaut. La confirmation
     * n'est valable que pour la situation effectivement enregistrée : si le
     * dossier est modifié ensuite, elle doit être refaite.
     */
    public function situationConfirmee(): bool
    {
        $confirmation = $this->derniereConfirmationSituation();

        return $confirmation !== null
            && $confirmation->observation === 'Situation confirmée : '.$this->resumeSituation();
    }

    /**
     * Résumé lisible de la situation, pour l'écran et l'historique.
     */
    public function resumeSituation(): string
    {
        if ($this->typeDossier?->code === '17') {
            return $this->interruption_contrat_plus_3_mois
                ? 'Interruption de contrat de plus de 3 mois : oui'
                : 'Interruption de contrat de plus de 3 mois : non';
        }

        $situation = match ($this->situation_familiale) {
            'marie' => 'Marié(e)',
            'celibataire' => 'Célibataire',
            default => 'Situation familiale non renseignée',
        };

        $details = [$situation, (int) $this->nombre_enfants.' enfant(s)'];

        if ($this->enfant_moins_de_4_ans) {
            $details[] = 'dont un de moins de 4 ans';
        }

        $details[] = $this->enfant_deja_integre
            ? 'enfant déjà intégré (CNAPS)'
            : 'aucun enfant intégré (CNAPS)';

        return implode(' · ', $details);
    }
}
