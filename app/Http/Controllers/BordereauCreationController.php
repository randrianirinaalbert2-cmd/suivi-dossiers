<?php

namespace App\Http\Controllers;

use App\Enums\StatutDossier;
use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\BordereauCreation;
use App\Models\BordereauRecu;
use App\Models\Destination;
use App\Models\Dossier;
use App\Models\Lieu;
use App\Models\LigneBordereauCreation;
use App\Models\TypeDossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BordereauCreationController extends Controller
{
    use PorteeParDivision;

    /**
     * Code « CMVT » des dossiers dont la ligne se saisit manuellement.
     *
     * Le recrutement d'un EFA fait naître l'agent et son matricule : le
     * dossier reçu ne contient donc pas encore les coordonnées définitives
     * à porter sur le bordereau de création.
     */
    public const CODE_SAISIE_MANUELLE = '03';

    public function index(Request $request): View
    {
        $filtres = $request->only(['q', 'annee', 'division_id', 'lieu_id', 'statut']);
        $statut = $filtres['statut'] ?? 'actifs';

        $requete = fn () => $this->porteeMatricule(BordereauCreation::query());

        $bordereaux = $this->porteeMatricule(BordereauCreation::query())
            ->with(['lieu', 'destination', 'division', 'utilisateur'])
            ->withCount('lignes')
            ->when($statut === 'archives', fn ($query) => $query->archives())
            ->when($statut === 'actifs', fn ($query) => $query->actifs())
            ->when($filtres['q'] ?? '', function ($query, $recherche) {
                $recherche = trim((string) $recherche);
                $query->where(function ($sous) use ($recherche) {
                    $sous->where('chapitre', 'like', "%{$recherche}%")
                        ->orWhere('paquet', 'like', "%{$recherche}%")
                        ->orWhere('num_ordre', 'like', "%{$recherche}%")
                        ->orWhereHas('lignes', fn ($ligne) => $ligne
                            ->where('agent_im', 'like', "%{$recherche}%")
                            ->orWhere('agent_nom', 'like', "%{$recherche}%")
                            ->orWhere('agent_prenoms', 'like', "%{$recherche}%"));
                });
            })
            ->when($filtres['annee'] ?? null, fn ($query, $annee) => $query->where('annee', $annee))
            ->when($filtres['lieu_id'] ?? null, fn ($query, $id) => $query->where('lieu_id', $id))
            ->orderByDesc('annee')
            ->orderByDesc('num_ordre')
            ->paginate(15)
            ->withQueryString();

        return view('bordereaux-creation.index', [
            'bordereaux' => $bordereaux,
            'filtres' => $filtres,
            'statut' => $statut,
            'nbActifs' => $requete()->actifs()->count(),
            'nbArchives' => $requete()->archives()->count(),
            'annees' => $this->porteeMatricule(BordereauCreation::query())
                ->select('annee')
                ->distinct()
                ->orderByDesc('annee')
                ->pluck('annee'),
            'lieux' => Lieu::orderBy('code')->get(),
            'divisions' => $this->divisionsVisibles(),
        ]);
    }

    public function create(Request $request): View
    {
        $divisionMatricule = $this->divisionMatricule();

        return view('bordereaux-creation.form', [
            'bordereau' => new BordereauCreation([
                'division_id' => $divisionMatricule?->id,
                'annee' => now()->year,
                'num_ordre' => BordereauCreation::prochainNumero(now()->year),
                'date_edition' => now()->toDateString(),
                'observation' => "En ayant l'honneur de vous transmettre « POUR COMPETENCE »",
            ]),
            'divisions' => $this->divisionsVisibles(),
            'lieux' => Lieu::orderBy('code')->get(),
            'destinations' => Destination::orderBy('nom')->get(),
            'typesDossiers' => $this->typesDossiersVisibles(),
        ]);
    }

    /**
     * Prépare un bordereau de création à partir des dossiers d'un bordereau
     * reçu : l'en-tête reste à compléter, les agents sont repris des dossiers
     * sauf pour le code « CMVT 03 » (recrutement d'un EFA) dont la ligne est
     * volontairement laissée vide pour une saisie manuelle.
     */
    public function createDepuisBordereau(Request $request, BordereauRecu $bordereau): View|RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        $divisionMatricule = $this->divisionMatricule();

        abort_unless(
            $divisionMatricule !== null && $request->user()->peutGererBordereauxDeCreation(),
            403
        );

        $dossiers = $bordereau->dossiers()
            ->with(['agent', 'typeDossier'])
            ->whereHas('typeDossier', fn ($query) => $query->where('division_id', $divisionMatricule->id))
            ->orderBy('id')
            ->get();

        if ($dossiers->isEmpty()) {
            return redirect()
                ->route('bordereaux.show', $bordereau)
                ->with('erreur', 'Aucun dossier de ce bordereau ne peut figurer sur un bordereau de création.');
        }

        $lignes = $dossiers->map(function (Dossier $dossier) {
            $saisieManuelle = $dossier->typeDossier?->code === self::CODE_SAISIE_MANUELLE;

            return new LigneBordereauCreation([
                'agent_id' => $saisieManuelle ? null : $dossier->agent_id,
                'agent_im' => $saisieManuelle ? null : $dossier->agent?->im,
                'agent_nom' => $saisieManuelle ? null : $dossier->agent?->nom,
                'agent_prenoms' => $saisieManuelle ? null : $dossier->agent?->prenoms,
                'type_dossier_id' => $dossier->type_dossier_id,
            ]);
        });

        $dateArrivee = $bordereau->date_arrivee?->format('d/m/Y');

        $bordereauCreation = new BordereauCreation([
            'division_id' => $divisionMatricule->id,
            // Le lieu (donc le sigle) vient de la localité du bordereau reçu.
            'lieu_id' => $bordereau->lieu_id,
            'annee' => now()->year,
            'num_ordre' => BordereauCreation::prochainNumero(now()->year),
            'date_edition' => now()->toDateString(),
            'observation' => "En ayant l'honneur de vous transmettre pour compétence"
                ." (bordereau reçu n° {$bordereau->num_arrivee}"
                .($dateArrivee !== null ? " du {$dateArrivee}" : '').')',
        ]);

        $bordereauCreation->setRelation('lignes', $lignes);

        return view('bordereaux-creation.form', [
            'bordereau' => $bordereauCreation,
            'sourceBordereau' => $bordereau,
            'divisions' => $this->divisionsVisibles(),
            'lieux' => Lieu::orderBy('code')->get(),
            'destinations' => Destination::orderBy('nom')->get(),
            'typesDossiers' => $this->typesDossiersVisibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->valider($request);

        $bordereau = DB::transaction(function () use ($donnees, $request) {
            $bordereau = BordereauCreation::create($donnees['bordereau'] + [
                'utilisateur_id' => $request->user()->id,
            ]);

            foreach ($donnees['lignes'] as $ligne) {
                $bordereau->lignes()->create($ligne);
            }

            return $bordereau;
        });

        return redirect()
            ->route('bordereaux-creation.show', $bordereau)
            ->with('succes', 'Le bordereau de création n° '.$bordereau->num_ordre.' a été enregistré.');
    }

    public function show(BordereauCreation $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->load(['lieu', 'destination', 'division', 'utilisateur', 'archivePar', 'lignes.agent', 'lignes.typeDossier']);

        return view('bordereaux-creation.show', compact('bordereau'));
    }

    public function edit(BordereauCreation $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->load('lignes.agent');

        return view('bordereaux-creation.form', [
            'bordereau' => $bordereau,
            'divisions' => $this->divisionsVisibles(),
            'lieux' => Lieu::orderBy('code')->get(),
            'destinations' => Destination::orderBy('nom')->get(),
            'typesDossiers' => $this->typesDossiersVisibles(),
        ]);
    }

    public function update(Request $request, BordereauCreation $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        $donnees = $this->valider($request, $bordereau);

        DB::transaction(function () use ($bordereau, $donnees) {
            $bordereau->update($donnees['bordereau']);
            $bordereau->lignes()->delete();

            foreach ($donnees['lignes'] as $ligne) {
                $bordereau->lignes()->create($ligne);
            }
        });

        return redirect()
            ->route('bordereaux-creation.show', $bordereau)
            ->with('succes', 'Le bordereau de création a été mis à jour.');
    }

    public function destroy(BordereauCreation $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->delete();

        return redirect()->route('bordereaux-creation.index')->with('succes', 'Le bordereau de création a été supprimé.');
    }

    public function archiver(Request $request, BordereauCreation $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        if (! $bordereau->estArchive()) {
            $bordereau->update([
                'statut' => 'archive',
                'archive_le' => now(),
                'archive_par_id' => $request->user()->id,
            ]);
        }

        return redirect()->route('bordereaux-creation.show', $bordereau)
            ->with('succes', 'Le bordereau de création a été archivé.');
    }

    public function desarchiver(BordereauCreation $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->update([
            'statut' => 'valide',
            'archive_le' => null,
            'archive_par_id' => null,
        ]);

        return redirect()->route('bordereaux-creation.show', $bordereau)
            ->with('succes', 'Le bordereau de création a été désarchivé.');
    }

    /**
     * Modèle du bordereau d'envoi (le BE lui-même), à remplir et signer.
     * Le « Soit transmis » est un document distinct, imprimé ensuite.
     */
    public function imprimer(BordereauCreation $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->load([
            'lieu', 'destination', 'division.chefDivision', 'utilisateur',
            'lignes.agent', 'lignes.typeDossier',
        ]);

        return view('bordereaux-creation.print', compact('bordereau'));
    }

    /**
     * Note « SOIT TRANSMIS » qui accompagne le bordereau d'envoi.
     *
     * Le nombre d'actes formatés n'est pas enregistré : il se saisit, en
     * option, juste avant l'impression de cette note.
     */
    public function soitTransmis(Request $request, BordereauCreation $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->load(['lieu', 'destination', 'division.chefDivision', 'utilisateur', 'lignes.agent', 'lignes.typeDossier']);

        $nombreActes = $request->filled('actes')
            ? max(0, (int) $request->input('actes'))
            : null;

        return view('bordereaux-creation.soit-transmis', compact('bordereau', 'nombreActes'));
    }

    /**
     * Note « SOIT TRANSMIS » établie à partir d'un bordereau reçu, une fois
     * les dossiers traités : le sigle vient de la localité du bordereau, les
     * mouvements (CMVT) et les IM viennent des dossiers. Le paquet, le nombre
     * d'actes formatés et l'IM des agents du code 03 (matricule à attribuer)
     * se saisissent directement sur la page avant l'impression.
     */
    public function soitTransmisDepuisBordereau(Request $request, BordereauRecu $bordereau): View|RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        // Le nom du signataire de la note vient du chef de la division.
        $bordereau->loadMissing('division.chefDivision');

        $divisionMatricule = $this->divisionMatricule();

        abort_unless(
            $divisionMatricule !== null && $request->user()->peutGererBordereauxDeCreation(),
            403
        );

        // Seuls les dossiers dont le traitement est terminé partent au service central.
        $dossiers = $bordereau->dossiers()
            ->with(['agent', 'typeDossier'])
            ->whereHas('typeDossier', fn ($query) => $query->where('division_id', $divisionMatricule->id))
            ->whereIn('statut', [
                StatutDossier::Traite->value,
                StatutDossier::Verifie->value,
                StatutDossier::Transmis->value,
                StatutDossier::Archive->value,
            ])
            ->orderBy('id')
            ->get();

        if ($dossiers->isEmpty()) {
            return redirect()
                ->route('bordereaux.show', $bordereau)
                ->with('erreur', 'Aucun dossier traité de ce bordereau ne peut figurer sur un « Soit transmis ».');
        }

        $lignes = $dossiers->map(function (Dossier $dossier) {
            $saisieManuelle = $dossier->typeDossier?->code === self::CODE_SAISIE_MANUELLE;

            $ligne = new LigneBordereauCreation([
                'agent_im' => $saisieManuelle ? null : $dossier->agent?->im,
                'type_dossier_id' => $dossier->type_dossier_id,
            ]);

            return $ligne->setRelation('typeDossier', $dossier->typeDossier);
        });

        $note = new BordereauCreation([
            'lieu_id' => $bordereau->lieu_id,
            'paquet' => $request->input('paquet'),
        ]);

        $note->setRelation('lieu', $bordereau->lieu);
        $note->setRelation('lignes', $lignes);

        $nombreActes = $request->filled('actes')
            ? max(0, (int) $request->input('actes'))
            : null;

        return view('bordereaux-creation.soit-transmis', [
            'bordereau' => $note,
            'nombreActes' => $nombreActes,
            'depuisRecu' => $bordereau,
        ]);
    }

    /**
     * @return array{bordereau: array<string, mixed>, lignes: array<int, array<string, mixed>>}
     */
    protected function valider(Request $request, ?BordereauCreation $bordereau = null): array
    {
        $donnees = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'lieu_id' => ['required', 'exists:lieux,id'],
            'destination_id' => ['required', 'exists:destinations,id'],
            'chapitre' => ['required', 'string', 'max:6'],
            'paquet' => ['nullable', 'string', 'max:10'],
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'num_ordre' => [
                'required', 'integer', 'min:1', 'max:999999',
                Rule::unique('bordereaux_creation', 'num_ordre')
                    ->where('annee', $request->integer('annee'))
                    ->ignore($bordereau?->id),
            ],
            'date_edition' => ['nullable', 'date'],
            'observation' => ['nullable', 'string', 'max:255'],
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.agent_im' => ['required', 'string', 'max:30'],
            'lignes.*.agent_nom' => ['required', 'string', 'max:100'],
            'lignes.*.agent_prenoms' => ['nullable', 'string', 'max:150'],
            'lignes.*.type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'lignes.*.observation' => ['nullable', 'string', 'max:255'],
        ], [
            'lignes.required' => 'Ajoutez au moins une ligne au bordereau.',
            'lignes.min' => 'Ajoutez au moins une ligne au bordereau.',
            'lignes.*.agent_im.required' => 'Renseignez l\'IM de l\'agent sur chaque ligne.',
            'lignes.*.agent_nom.required' => 'Renseignez le nom de l\'agent sur chaque ligne.',
        ], [
            'division_id' => 'division',
            'lieu_id' => 'lieu',
            'destination_id' => 'destination',
            'chapitre' => 'chapitre',
            'paquet' => 'paquet',
            'annee' => 'année',
            'num_ordre' => 'numéro d\'ordre',
            'date_edition' => 'date d\'édition',
            'lignes.*.agent_im' => 'IM de l\'agent',
            'lignes.*.agent_nom' => 'nom de l\'agent',
            'lignes.*.agent_prenoms' => 'prénoms de l\'agent',
        ]);

        $this->verifierDivision((int) $donnees['division_id']);

        $divisionMatricule = $this->divisionMatricule();

        if ($divisionMatricule === null || (int) $donnees['division_id'] !== (int) $divisionMatricule->id) {
            throw ValidationException::withMessages([
                'division_id' => 'Les bordereaux de création (porteurs de la matricule des agents) relèvent de la division « '
                    .($divisionMatricule?->nom ?? 'Solde').' ».',
            ]);
        }

        $lignes = [];
        $cles = [];

        foreach ($donnees['lignes'] as $ligne) {
            $type = TypeDossier::find($ligne['type_dossier_id']);

            if ($type === null || (int) $type->division_id !== (int) $divisionMatricule->id) {
                throw ValidationException::withMessages([
                    'lignes' => 'Un type de dossier n\'appartient pas à la division choisie.',
                ]);
            }

            $im = trim($ligne['agent_im']);
            $cle = mb_strtolower($im).'-'.$ligne['type_dossier_id'];

            if (isset($cles[$cle])) {
                throw ValidationException::withMessages([
                    'lignes' => 'Une ligne en double a été détectée (même IM et même type de dossier).',
                ]);
            }

            $cles[$cle] = true;

            $lignes[] = [
                'agent_im' => $im,
                'agent_nom' => trim($ligne['agent_nom']),
                'agent_prenoms' => isset($ligne['agent_prenoms']) ? trim($ligne['agent_prenoms']) : null,
                'type_dossier_id' => (int) $ligne['type_dossier_id'],
                'observation' => $ligne['observation'] ?? null,
            ];
        }

        return [
            'bordereau' => [
                'division_id' => (int) $donnees['division_id'],
                'lieu_id' => (int) $donnees['lieu_id'],
                'destination_id' => (int) $donnees['destination_id'],
                'chapitre' => $donnees['chapitre'],
                'paquet' => $donnees['paquet'] ?? null,
                'annee' => (int) $donnees['annee'],
                'num_ordre' => (int) $donnees['num_ordre'],
                'date_edition' => $donnees['date_edition'] ?? null,
                'observation' => $donnees['observation'] ?? null,
            ],
            'lignes' => $lignes,
        ];
    }

    /**
     * Seule la division qui tient la matricule figure dans le formulaire.
     */
    protected function divisionsVisibles(): Collection
    {
        $division = $this->divisionMatricule();

        return $division === null ? collect() : collect([$division]);
    }

    /**
     * Types de dossiers proposés : ceux de la division qui tient la
     * matricule, seuls compatibles avec le bordereau de création.
     *
     * @return Collection<int, TypeDossier>
     */
    protected function typesDossiersVisibles()
    {
        return $this->porteeMatricule(TypeDossier::query())->with('division')->orderBy('code')->get();
    }
}
