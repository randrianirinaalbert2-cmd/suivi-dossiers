<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BordereauRecuController extends Controller
{
    use PorteeParDivision;

    /**
     * Valeur sentinelle de l'option « Autre » du champ service d'origine.
     */
    public const SERVICE_AUTRE = '__autre__';

    /**
     * Objet porté par les dossiers de la division Solde : ils sont traités en mandatement.
     */
    public const OBJET_MANDATEMENT = 'POUR MANDATEMENT';

    /**
     * Objet porté par les dossiers de la division Visa : ils sont soumis au visa.
     */
    public const OBJET_VISA = 'POUR VISA';

    /**
     * Libellés « par défaut » d'objet : ils dépendent de la division et ne
     * doivent jamais être mélangés entre divisions.
     *
     * @var list<string>
     */
    public const OBJETS_PAR_DEFAUT = [self::OBJET_MANDATEMENT, self::OBJET_VISA];

    public function index(Request $request): View
    {
        $filtres = $request->only(['q', 'division_id', 'service_origine_id', 'du', 'au']);

        $bordereaux = $this->appliquerPortee(BordereauRecu::query())
            ->with(['serviceOrigine', 'division', 'utilisateur'])
            ->withCount('dossiers')
            ->when($filtres['q'] ?? '', function ($query, $recherche) {
                $recherche = trim((string) $recherche);
                $query->where(function ($sous) use ($recherche) {
                    $sous->where('reference', 'like', "%{$recherche}%")
                        ->orWhere('num_arrivee', 'like', "%{$recherche}%");
                });
            })
            ->when($filtres['division_id'] ?? null, fn ($query, $id) => $query->where('division_id', $id))
            ->when($filtres['service_origine_id'] ?? null, fn ($query, $id) => $query->where('service_origine_id', $id))
            ->when($filtres['du'] ?? null, fn ($query, $date) => $query->whereDate('date_arrivee', '>=', $date))
            ->when($filtres['au'] ?? null, fn ($query, $date) => $query->whereDate('date_arrivee', '<=', $date))
            ->orderByDesc('date_arrivee')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('bordereaux.index', [
            'bordereaux' => $bordereaux,
            'filtres' => $filtres,
            'services' => ServiceOrigine::orderBy('nom')->get(),
            'divisions' => $this->divisionsVisibles(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('bordereaux.form', [
            'bordereau' => new BordereauRecu([
                'division_id' => $divisionId = $this->divisionParDefaut($request->integer('division_id') ?: null),
                'objet' => self::objetParDefautPourDivision($divisionId),
                'date_bordereau' => now()->toDateString(),
                'date_arrivee' => now()->toDateString(),
            ]),
            'services' => ServiceOrigine::orderBy('nom')->get(),
            'lieux' => Lieu::orderBy('code')->get(),
            'divisions' => $this->divisionsVisibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->valider($request);

        $bordereau = BordereauRecu::create($donnees + ['utilisateur_id' => $request->user()->id]);

        return redirect()
            ->route('bordereaux.show', $bordereau)
            ->with('succes', 'Le bordereau a été enregistré. Vous pouvez maintenant créer les dossiers qu\'il contient.');
    }

    public function show(Request $request, BordereauRecu $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->load(['serviceOrigine', 'lieu', 'division', 'utilisateur', 'documents.utilisateur']);

        return view('bordereaux.show', [
            'bordereau' => $bordereau,
            'dossiers' => $bordereau->dossiers()->with(['agent', 'typeDossier.division', 'responsable'])->orderBy('id')->get(),
        ]);
    }

    public function imprimer(BordereauRecu $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->load(['serviceOrigine', 'division', 'lieu', 'utilisateur',
            'dossiers.agent', 'dossiers.typeDossier', 'dossiers.responsable']);

        return view('bordereaux.print', compact('bordereau'));
    }

    public function edit(BordereauRecu $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        return view('bordereaux.form', [
            'bordereau' => $bordereau,
            'services' => ServiceOrigine::orderBy('nom')->get(),
            'lieux' => Lieu::orderBy('code')->get(),
            'divisions' => $this->divisionsVisibles(),
        ]);
    }

    public function update(Request $request, BordereauRecu $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->update($this->valider($request, $bordereau));

        return redirect()
            ->route('bordereaux.show', $bordereau)
            ->with('succes', 'Le bordereau a été mis à jour.');
    }

    public function destroy(BordereauRecu $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        if ($bordereau->dossiers()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : des dossiers sont rattachés à ce bordereau.');
        }

        foreach ($bordereau->documents as $document) {
            $document->delete();
        }

        $bordereau->delete();

        return redirect()->route('bordereaux.index')->with('succes', 'Le bordereau a été supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function valider(Request $request, ?BordereauRecu $bordereau = null): array
    {
        $choix = (string) $request->input('service_origine_id', '');
        $nouveau = trim((string) $request->input('nouveau_service_origine', ''));

        // La valeur sentinelle « Autre » désigne un service à créer immédiatement.
        if ($choix === self::SERVICE_AUTRE) {
            if ($nouveau === '') {
                throw ValidationException::withMessages([
                    'nouveau_service_origine' => 'Indiquez le nom du nouveau service d\'origine.',
                ]);
            }

            $request->merge(['service_origine_id' => null]);
        }

        $donnees = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'service_origine_id' => ['nullable', 'exists:services_origine,id'],
            'lieu_id' => ['nullable', 'exists:lieux,id'],
            'nouveau_service_origine' => ['nullable', 'string', 'max:150'],
            'reference' => ['required', 'string', 'max:80'],
            'objet' => ['nullable', 'string', 'max:200'],
            'date_bordereau' => ['required', 'date'],
            'num_arrivee' => ['required', 'string', 'max:10'],
            'date_arrivee' => ['required', 'date'],
            'observation' => ['nullable', 'string', 'max:255'],
            'nombre_dossiers_attendus' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [], [
            'division_id' => 'division',
            'service_origine_id' => 'service d\'origine',
            'lieu_id' => 'localité',
            'reference' => 'référence',
            'objet' => 'objet',
            'date_bordereau' => 'date du bordereau',
            'num_arrivee' => 'numéro d\'arrivée',
            'date_arrivee' => 'date d\'arrivée',
            'nombre_dossiers_attendus' => 'nombre de dossiers',
        ]);

        $this->verifierDivision((int) $donnees['division_id']);

        $donnees['objet'] = $this->normaliserObjet((int) $donnees['division_id'], $donnees['objet'] ?? null);

        if (! ($donnees['service_origine_id'] ?? null) && $nouveau !== '') {
            $donnees['service_origine_id'] = ServiceOrigine::firstOrCreate(['nom' => $nouveau])->id;
        }

        unset($donnees['nouveau_service_origine']);

        if (! ($donnees['service_origine_id'] ?? null)) {
            throw ValidationException::withMessages([
                'service_origine_id' => 'Veuillez sélectionner un service d\'origine.',
            ]);
        }

        $reference = BordereauRecu::where('service_origine_id', $donnees['service_origine_id'])
            ->where('reference', $donnees['reference'])
            ->when($bordereau !== null, fn ($query) => $query->where('id', '!=', $bordereau->id))
            ->exists();

        if ($reference) {
            throw ValidationException::withMessages([
                'reference' => 'Ce bordereau existe déjà pour ce service d\'origine.',
            ]);
        }

        return $donnees;
    }

    /**
     * Normalise l'objet du bordereau.
     *
     * L'objet vaut celui de la division (« POUR MANDATEMENT » pour Solde,
     * « POUR VISA » pour Visa). Tout libellé « par défaut » — quelle que soit
     * sa casse — appartenant à une autre division est réaligné sur la
     * division choisie, afin qu'un bordereau Visa ne puisse jamais porter
     * « POUR MANDATEMENT » (et inversement). Un objet saisi librement
     * (ex. « POUR RETRAITE ») est en revanche conservé.
     */
    protected function normaliserObjet(int $divisionId, ?string $objet): ?string
    {
        $defaut = self::objetParDefautPourDivision($divisionId);
        $saisi = trim((string) $objet);

        if ($saisi === '') {
            return $defaut;
        }

        $estLibelleParDefaut = in_array(
            mb_strtoupper($saisi),
            array_map('mb_strtoupper', self::OBJETS_PAR_DEFAUT),
            true
        );

        if ($estLibelleParDefaut) {
            // Un libellé « par défaut » suit toujours la division choisie.
            return $defaut ?? $saisi;
        }

        return $saisi;
    }

    /**
     * Objet par défaut du bordereau, déduit de sa division :
     * « POUR MANDATEMENT » pour la division Solde, « POUR VISA » pour la
     * division Visa, rien pour les autres divisions.
     */
    public static function objetParDefautPourDivision(?int $divisionId): ?string
    {
        if ($divisionId === null) {
            return null;
        }

        return match (Division::whereKey($divisionId)->value('nom')) {
            Division::NOM_SOLDE => self::OBJET_MANDATEMENT,
            Division::NOM_VISA => self::OBJET_VISA,
            default => null,
        };
    }
}
