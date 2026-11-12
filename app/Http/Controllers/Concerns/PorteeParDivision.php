<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\PoleDossier;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Délimite les requêtes à la division de l'utilisateur connecté.
 *
 * L'administrateur voit toutes les divisions ; les autres rôles ne voient
 * que les enregistrements de leur propre division.
 */
trait PorteeParDivision
{
    protected function utilisateurCourant(): ?User
    {
        return auth()->user();
    }

    /**
     * Toutes les divisions sont-elles visibles ?
     */
    protected function voitToutesLesDivisions(): bool
    {
        return $this->utilisateurCourant()?->divisionsAccessibles() === null;
    }

    /**
     * Applique le filtre de division à une requête.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     */
    protected function appliquerPortee(Builder $query, string $colonne = 'division_id'): Builder
    {
        $divisions = $this->utilisateurCourant()?->divisionsAccessibles();

        if ($divisions === null) {
            return $query;
        }

        return $query->whereIn($colonne, $divisions);
    }

    /**
     * Pôles naturels des divisions accessibles.
     *
     * L'administrateur et le chef de service (hiérarchique) voient toutes les
     * divisions : la méthode renvoie null (aucun filtre). Un utilisateur
     * rattaché à une division ne voit que le pôle de cette division.
     *
     * @return array<int, string>|null
     */
    protected function polesAccessibles(): ?array
    {
        return $this->utilisateurCourant()?->polesAccessibles();
    }

    /**
     * Restreint une requête aux pôles accessibles à l'utilisateur.
     *
     * Les dossiers sans pôle sont considérés comme relevant du pôle Solde
     * (valeur appliquée par défaut à la création).
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     */
    protected function limiterAuxPoles(Builder $query, ?array $poles, string $colonne = 'pole'): Builder
    {
        if ($poles === null) {
            return $query;
        }

        if ($poles === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $sousRequete) use ($poles, $colonne) {
            $sousRequete->whereIn($colonne, $poles);

            if (in_array(PoleDossier::Solde->value, $poles, true)) {
                $sousRequete->orWhereNull($colonne);
            }
        });
    }

    /**
     * Division retenue pour la création d'un enregistrement.
     */
    protected function divisionParDefaut(?int $divisionId): ?int
    {
        $utilisateur = $this->utilisateurCourant();

        if ($utilisateur === null) {
            return null;
        }

        if ($utilisateur->voitToutesLesDivisions()) {
            return $divisionId;
        }

        $division = $utilisateur->division_id;

        return $divisionId !== null && (int) $divisionId === (int) $division
            ? (int) $division
            : ($division !== null ? (int) $division : null);
    }

    /**
     * Vérifie que l'utilisateur accède bien à la division de l'enregistrement.
     */
    protected function verifierDivision(?int $divisionId): void
    {
        abort_unless($this->utilisateurCourant()?->voitDivision($divisionId) ?? false, 403);
    }

    /**
     * Divisions proposées dans les listes déroulantes.
     *
     * @return Collection<int, Division>
     */
    protected function divisionsVisibles(): Collection
    {
        $divisions = Division::orderBy('nom')->get();

        if ($this->voitToutesLesDivisions()) {
            return $divisions;
        }

        return $divisions
            ->filter(fn (Division $division) => $this->utilisateurCourant()?->voitDivision($division->id))
            ->values();
    }

    /**
     * Division qui tient la matricule des agents.
     */
    protected function divisionMatricule(): ?Division
    {
        return Division::divisionMatricule();
    }

    /**
     * Restreint la requête à la division qui tient la matricule des agents.
     *
     * Les bordereaux de création portent l'IM (matricule) des agents : ils
     * relèvent de la seule division qui crée la matricule.
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     */
    protected function porteeMatricule(Builder $query): Builder
    {
        $division = $this->divisionMatricule();

        return $division === null ? $query : $query->where('division_id', $division->id);
    }
}
