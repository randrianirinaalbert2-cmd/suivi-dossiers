<?php

namespace App\Http\Controllers;

use App\Enums\PoleDossier;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\Historique;
use App\Models\TypeDossier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DossierController extends Controller
{
    use PorteeParDivision;

    public function index(Request $request): View
    {
        $filtres = $request->only(['q', 'statut', 'pole', 'type_dossier_id', 'division_id', 'du', 'au', 'mois_arrivee', 'mois_exploitation']);

        // Le mois d'exploitation est une donnée du pôle Solde : un compte
        // rattaché au Visa ne doit ni le filtrer ni le voir. Le mois d'arrivée
        // reste disponible pour tout le monde.
        $polesAccessibles = $request->user()->polesAccessibles();
        $voitMoisExploitation = $polesAccessibles === null
            || in_array(PoleDossier::Solde->value, $polesAccessibles, true);

        if (! $voitMoisExploitation) {
            unset($filtres['mois_exploitation']);
        }

        $dossiers = $this->appliquerPortee(Dossier::query())
            ->with(['agent', 'typeDossier', 'division', 'responsable', 'bordereauRecu'])
            ->when($filtres['q'] ?? '', function ($query, $recherche) {
                $recherche = trim((string) $recherche);
                $query->where(function ($sous) use ($recherche) {
                    $sous->whereHas('agent', function ($q) use ($recherche) {
                        $q->where('im', 'like', "%{$recherche}%")
                            ->orWhere('nom', 'like', "%{$recherche}%")
                            ->orWhere('prenoms', 'like', "%{$recherche}%");
                    })->orWhereHas('bordereauRecu', function ($q) use ($recherche) {
                        $q->where('reference', 'like', "%{$recherche}%")
                            ->orWhere('num_arrivee', 'like', "%{$recherche}%");
                    });
                });
            })
            ->when($filtres['statut'] ?? '', fn ($query, $statut) => $query->where('statut', $statut))
            ->when($filtres['pole'] ?? '', fn ($query, $pole) => $query->where('pole', $pole))
            ->when($filtres['type_dossier_id'] ?? null, fn ($query, $id) => $query->where('type_dossier_id', $id))
            ->when($filtres['division_id'] ?? null, fn ($query, $id) => $query->where('division_id', $id))
            ->when($filtres['du'] ?? null, fn ($query, $date) => $query->whereDate('cree_le', '>=', $date))
            ->when($filtres['au'] ?? null, fn ($query, $date) => $query->whereDate('cree_le', '<=', $date))
            ->when($filtres['mois_arrivee'] ?? null, function ($query, $mois) {
                if (preg_match('/^(\d{4})-(\d{2})$/', (string) $mois, $m)) {
                    $query->whereHas('bordereauRecu', function ($q) use ($m) {
                        $q->whereYear('date_arrivee', (int) $m[1])->whereMonth('date_arrivee', (int) $m[2]);
                    });
                }
            })
            ->when($filtres['mois_exploitation'] ?? null, function ($query, $mois) {
                if (preg_match('/^\d{4}-\d{2}$/', (string) $mois)) {
                    $query->where('mois_exploitation', $mois)
                        ->where('pole', '!=', PoleDossier::Visa->value);
                }
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $compteurs = [];
        foreach (StatutDossier::cases() as $statut) {
            $compteurs[$statut->value] = $this->appliquerPortee(Dossier::query())->where('statut', $statut->value)->count();
        }

        $compteursPole = [];
        foreach (PoleDossier::cases() as $pole) {
            $compteursPole[$pole->value] = $this->appliquerPortee(Dossier::query())->where('pole', $pole->value)->count();
        }

        return view('dossiers.index', [
            'dossiers' => $dossiers,
            'filtres' => $filtres,
            'compteurs' => $compteurs,
            'compteursPole' => $compteursPole,
            'statuts' => StatutDossier::cases(),
            'poles' => PoleDossier::cases(),
            'typesDossiers' => $this->typesDossiersVisibles(),
            'divisions' => $this->divisionsVisibles(),
            'voitMoisExploitation' => $voitMoisExploitation,
        ]);
    }

    public function create(Request $request): View
    {
        $bordereauId = $request->integer('bordereau_recu_id') ?: null;
        $bordereau = $bordereauId !== null
            ? BordereauRecu::find($bordereauId)
            : $this->dernierBordereauDisponible();

        if ($bordereau !== null) {
            $this->verifierDivision($bordereau->division_id);
        }

        return view('dossiers.form', [
            'dossier' => new Dossier(['statut' => StatutDossier::Recu]),
            'bordereau' => $bordereau,
            'bordereaux' => $this->bordereauxDisponibles(),
            'typesDossiers' => $bordereau !== null
                ? $this->typesDossiersVisibles()->where('division_id', $bordereau->division_id)->values()
                : $this->typesDossiersVisibles(),
            'agents' => $this->agentsDisponibles(),
            'responsables' => $this->responsablesDisponibles($bordereau?->division_id),
            'poles' => PoleDossier::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->valider($request);

        $bordereau = BordereauRecu::findOrFail($donnees['bordereau_recu_id']);
        $this->verifierDivision($bordereau->division_id);

        $dossier = Dossier::create($donnees + [
            'division_id' => $bordereau->division_id,
            'statut' => StatutDossier::Recu,
        ]);

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $request->user()->id,
            'type_action' => 'creation',
            'nouveau_statut' => StatutDossier::Recu->value,
            'observation' => 'Enregistrement du dossier depuis le bordereau n° '.$bordereau->num_arrivee,
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Le dossier a été créé. Vérifiez les pièces exigées ci-dessous.');
    }

    /**
     * Saisie en masse : enregistrer d'un coup tous les agents portés sur un bordereau papier.
     */
    public function createPlusieurs(Request $request, BordereauRecu $bordereau): View
    {
        $this->verifierDivision($bordereau->division_id);

        return view('dossiers.plusieurs', [
            'bordereau' => $bordereau,
            'typesDossiers' => $this->typesDossiersVisibles()
                ->where('division_id', $bordereau->division_id)
                ->values(),
            'responsables' => $this->responsablesDisponibles($bordereau->division_id),
            'poles' => PoleDossier::cases(),
            'poleParDefaut' => $this->poleDeLaDivision($bordereau->division_id),
            'nbLignes' => min(50, max(1, (int) ($bordereau->nombre_dossiers_attendus ?? 5))),
        ]);
    }

    protected function poleDeLaDivision(?int $divisionId): PoleDossier
    {
        return Division::whereKey($divisionId)->first()?->poleNaturel() ?? PoleDossier::Solde;
    }

    public function show(Request $request, Dossier $dossier): View
    {
        $this->verifierDivision($dossier->division_id);

        $dossier->load([
            'agent', 'typeDossier.piecesRequises.typePiece', 'division',
            'responsable', 'bordereauRecu.serviceOrigine',
            'documents.utilisateur', 'historiques.utilisateur',
            'piecesRecues.typePiece',
        ]);

        $dossier->setRelation('historiques', $dossier->historiques->sortByDesc('date_heure_action')->values());

        return view('dossiers.show', [
            'dossier' => $dossier,
            'completude' => $dossier->completude(),
            'piecesExigees' => $dossier->typeDossier->piecesRequises->sortBy('typePiece.libelle')->values(),
            'piecesRecues' => $dossier->piecesRecues->keyBy('type_piece_id'),
            'suite' => $this->statutsPermis($request->user(), $dossier),
            'suiteBloquee' => $dossier->statut->suite() !== []
                && $this->statutsPermis($request->user(), $dossier) === [],
        ]);
    }

    /**
     * Saisie en masse des dossiers portés par un bordereau reçu.
     */
    public function enregistrerEnMasse(Request $request, BordereauRecu $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        // On écarte les lignes totalement vides (le formulaire propose des rangs de trop).
        $lignes = collect($request->input('dossiers', []))
            ->filter(fn ($ligne) => is_array($ligne)
                && trim((string) ($ligne['agent_im'] ?? '')) !== ''
                || (is_array($ligne) && trim((string) ($ligne['agent_nom'] ?? '')) !== '')
                || (is_array($ligne) && ! empty($ligne['type_dossier_id'])))
            ->values();

        $request->merge(['dossiers' => $lignes->all()]);

        $donnees = $request->validate([
            'pole' => ['nullable', 'in:'.implode(',', PoleDossier::valeurs())],
            'responsable_id' => ['nullable', 'exists:utilisateurs,id'],
            'dossiers' => ['required', 'array', 'min:1'],
            'dossiers.*.agent_im' => ['required', 'string', 'max:30'],
            'dossiers.*.agent_nom' => ['required', 'string', 'max:100'],
            'dossiers.*.agent_prenoms' => ['nullable', 'string', 'max:150'],
            'dossiers.*.type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'dossiers.*.observation' => ['nullable', 'string', 'max:1000'],
        ], [
            'dossiers.required' => 'Ajoutez au moins une ligne.',
            'dossiers.min' => 'Ajoutez au moins une ligne.',
            'dossiers.*.agent_im.required' => 'Renseignez l\'IM de l\'agent sur chaque ligne.',
            'dossiers.*.agent_nom.required' => 'Renseignez le nom de l\'agent sur chaque ligne.',
        ], [
            'pole' => 'pôle',
            'dossiers.*.agent_im' => 'IM de l\'agent',
            'dossiers.*.agent_nom' => 'nom de l\'agent',
            'dossiers.*.agent_prenoms' => 'prénoms de l\'agent',
            'dossiers.*.type_dossier_id' => 'type de dossier',
        ]);

        foreach ($donnees['dossiers'] as $ligne) {
            $type = TypeDossier::find($ligne['type_dossier_id']);

            if ($type === null || (int) $type->division_id !== (int) $bordereau->division_id) {
                throw ValidationException::withMessages([
                    'dossiers' => 'Un type de dossier n\'appartient pas à la division du bordereau.',
                ]);
            }
        }

        $this->refuserDoublons($donnees['dossiers']);

        $pole = PoleDossier::from($donnees['pole'] ?? $this->poleDeLaDivision($bordereau->division_id)->value);
        $responsableId = $donnees['responsable_id'] ?? null;
        $cree = 0;

        DB::transaction(function () use ($donnees, $bordereau, $request, $pole, $responsableId, &$cree) {
            foreach ($donnees['dossiers'] as $ligne) {
                $agent = $this->agentDepuisLigne($ligne);

                $dossier = Dossier::create([
                    'bordereau_recu_id' => $bordereau->id,
                    'division_id' => $bordereau->division_id,
                    'agent_id' => $agent->id,
                    'type_dossier_id' => $ligne['type_dossier_id'],
                    'responsable_id' => $responsableId ?? $request->user()->id,
                    'observation' => $ligne['observation'] ?? null,
                    'statut' => StatutDossier::Recu,
                    'pole' => $pole,
                ]);

                Historique::create([
                    'dossier_id' => $dossier->id,
                    'utilisateur_id' => $request->user()->id,
                    'type_action' => 'creation',
                    'nouveau_statut' => StatutDossier::Recu->value,
                    'observation' => 'Enregistrement en masse depuis le bordereau n° '.$bordereau->num_arrivee,
                ]);

                $cree++;
            }
        });

        $redirect = redirect()
            ->route('bordereaux.show', $bordereau)
            ->with('succes', $cree.' dossier(s) rattaché(s) au bordereau n° '.$bordereau->num_arrivee.'.');

        if ($bordereau->nombre_dossiers_attendus !== null
            && $bordereau->nombreDossiersSaisis() < (int) $bordereau->nombre_dossiers_attendus) {
            $redirect->with('erreur', 'Attention : le bordereau papier annonce '
                .$bordereau->nombre_dossiers_attendus.' dossier(s), seulement '
                .$bordereau->nombreDossiersSaisis().' sont saisis.');
        }

        return $redirect;
    }

    /**
     * Un agent ne peut pas apparaître deux fois avec le même type de dossier.
     *
     * @param  array<int, array<string, mixed>>  $lignes
     */
    protected function refuserDoublons(array $lignes): void
    {
        $cles = [];

        foreach ($lignes as $ligne) {
            $cle = $this->normaliserIm((string) $ligne['agent_im']).'-'.$ligne['type_dossier_id'];

            if (isset($cles[$cle])) {
                throw ValidationException::withMessages([
                    'dossiers' => 'Une ligne en double a été détectée (même IM et même type de dossier).',
                ]);
            }

            $cles[$cle] = true;
        }
    }

    /**
     * L'agent est recherché par IM dans le référentiel global, créé s'il est inconnu.
     */
    protected function agentDepuisLigne(array $ligne): Agent
    {
        $im = trim($ligne['agent_im']);
        $normalise = $this->normaliserIm($im);

        $agent = Agent::query()
            ->where('im', $im)
            ->orWhere('im', $normalise)
            ->orWhereRaw("REPLACE(REPLACE(REPLACE(UPPER(im), ' ', ''), '.', ''), '-', '') = ?", [$normalise])
            ->first();

        if ($agent !== null) {
            return $agent;
        }

        return Agent::create([
            'im' => $im,
            'nom' => trim($ligne['agent_nom']),
            'prenoms' => isset($ligne['agent_prenoms']) ? trim($ligne['agent_prenoms']) : null,
        ]);
    }

    /**
     * L'agent vient soit de la liste (agent_id), soit de la saisie libre (IM, nom, prénoms).
     *
     * @param  array<string, mixed>  $donnees
     */
    protected function agentDepuisSaisie(array $donnees): Agent
    {
        if (! empty($donnees['agent_id'])) {
            return Agent::findOrFail($donnees['agent_id']);
        }

        return $this->agentDepuisLigne([
            'agent_im' => (string) ($donnees['agent_im'] ?? ''),
            'agent_nom' => (string) ($donnees['agent_nom'] ?? ''),
            'agent_prenoms' => $donnees['agent_prenoms'] ?? null,
        ]);
    }

    /**
     * Les IM sont écrits « 382 515 » sur les bordereaux papier et parfois sans espace.
     */
    protected function normaliserIm(string $im): string
    {
        return mb_strtoupper(preg_replace('/[\s.\-]+/', '', trim($im)));
    }

    public function edit(Dossier $dossier): View
    {
        $this->verifierDivision($dossier->division_id);

        $bordereau = $dossier->bordereauRecu;

        return view('dossiers.form', [
            'dossier' => $dossier,
            'bordereau' => $bordereau,
            'bordereaux' => $this->bordereauxDisponibles(),
            'typesDossiers' => $this->typesDossiersVisibles()->where('division_id', $dossier->division_id)->values(),
            'agents' => $this->agentsDisponibles(),
            'responsables' => $this->responsablesDisponibles($dossier->division_id),
            'poles' => PoleDossier::cases(),
        ]);
    }

    public function update(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->verifierDivision($dossier->division_id);

        $donnees = $this->valider($request);

        $bordereau = BordereauRecu::findOrFail($donnees['bordereau_recu_id']);
        $this->verifierDivision($bordereau->division_id);

        $dossier->update($donnees + ['division_id' => $bordereau->division_id]);

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $request->user()->id,
            'type_action' => 'modification',
            'ancien_statut' => $dossier->statut->value,
            'nouveau_statut' => $dossier->statut->value,
            'observation' => 'Mise à jour des informations du dossier',
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Le dossier a été mis à jour.');
    }

    /**
     * Changement de statut selon le workflow (circuit de validation).
     */
    public function changerStatut(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->verifierDivision($dossier->division_id);

        $regles = [
            'statut' => ['required', 'in:'.implode(',', StatutDossier::valeurs())],
            'observation' => ['nullable', 'string', 'max:1000'],
        ];

        // Le mois d'exploitation n'existe que pour le pôle Solde : pour un
        // dossier encore au Visa, la valeur éventuellement envoyée est ignorée.
        if ($dossier->utiliseMoisExploitation()) {
            $regles['mois_exploitation'] = ['nullable', 'date_format:Y-m'];
        }

        $donnees = $request->validate(
            $regles,
            [],
            ['observation' => 'observation', 'mois_exploitation' => 'mois d\'exploitation']
        );

        $nouveau = StatutDossier::from($donnees['statut']);

        // Un statut qui n'est plus atteignable (page restée ouverte, double
        // envoi, dossier modifié entre-temps) ne doit pas afficher une page
        // d'erreur 422 : on revient sur la fiche avec un message clair.
        if (! in_array($nouveau, $dossier->statut->suite(), true)) {
            return back()->with(
                'erreur',
                'Le dossier est déjà « '.$dossier->statut->label().' » : le passage à « '
                .$nouveau->label().' » n\'est plus possible. La page a été rechargée, réessayez.'
            );
        }

        if (! $request->user()->peutChangerStatut($dossier, $nouveau)) {
            return back()->with('erreur', match ($nouveau) {
                StatutDossier::Transmis => 'Seul le chef de division peut transmettre un dossier.',
                StatutDossier::Archive => 'Seul le responsable du dossier peut l\'archiver.',
                default => 'Vous n\'avez pas le droit de modifier ce statut.',
            });
        }

        $ancien = $dossier->statut;

        $maj = [
            'statut' => $nouveau,
            'observation' => $donnees['observation'] ?? $dossier->observation,
        ];
        if ($dossier->utiliseMoisExploitation()) {
            $maj['mois_exploitation'] = ($donnees['mois_exploitation'] ?? null) ?: null;
        }
        $dossier->update($maj);

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $request->user()->id,
            'type_action' => 'changement_statut',
            'ancien_statut' => $ancien->value,
            'nouveau_statut' => $nouveau->value,
            'observation' => $donnees['observation'] ?? null,
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Statut mis à jour : '.$ancien->label().' → '.$nouveau->label().'.');
    }

    /**
     * Statuts que l'utilisateur connecté a le droit de choisir pour ce dossier.
     *
     * @return array<int, StatutDossier>
     */
    protected function statutsPermis(User $utilisateur, Dossier $dossier): array
    {
        return array_values(array_filter(
            $dossier->statut->suite(),
            fn (StatutDossier $statut) => $utilisateur->peutChangerStatut($dossier, $statut),
        ));
    }

    /**
     * Visa : contrôle par le responsable puis transmission du dossier au pôle Solde.
     */
    public function transmettreAuSolde(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->verifierDivision($dossier->division_id);

        if ($dossier->pole !== PoleDossier::Visa) {
            return back()->with('erreur', 'Seul un dossier au pôle Visa peut être transmis au pôle Solde.');
        }

        $donnees = $request->validate([
            'num_visa' => ['nullable', 'string', 'max:20'],
            'date_visa' => ['nullable', 'date'],
            'reference_acte' => ['nullable', 'string', 'max:60'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'num_visa' => 'numéro de visa',
            'date_visa' => 'date du visa',
            'reference_acte' => "référence de l'acte",
            'observation' => 'observation',
        ]);

        $dossier->update([
            'pole' => PoleDossier::Solde,
            'passe_par_visa' => true,
            'num_visa' => ($donnees['num_visa'] ?? null) ?: $dossier->num_visa,
            'date_visa' => ($donnees['date_visa'] ?? null) ?: ($dossier->date_visa?->toDateString() ?? now()->toDateString()),
            'reference_acte' => ($donnees['reference_acte'] ?? null) ?: $dossier->reference_acte,
        ]);

        $observation = $donnees['observation'] ?? null;

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $request->user()->id,
            'type_action' => 'visa',
            'ancien_statut' => $dossier->statut->value,
            'nouveau_statut' => $dossier->statut->value,
            'observation' => 'Contrôle du visa effectué : dossier transmis au pôle Solde.'
                .(! empty($observation) ? ' — '.$observation : ''),
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Le dossier a été visé et transmis au pôle Solde.');
    }

    /**
     * Un responsable (ou membre habilité de la division) prend le dossier en charge.
     */
    public function prendreEnCharge(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->verifierDivision($dossier->division_id);

        $utilisateur = $request->user();

        if (! $utilisateur->estAdministrateur() && $utilisateur->division_id !== $dossier->division_id) {
            abort(403);
        }

        // Identité de la personne qui prend physiquement le dossier : elle peut différer
        // du compte connecté, on l'enregistre donc dans l'historique au moment de la prise.
        $donnees = $request->validate([
            'pris_par_im' => ['required', 'string', 'max:50'],
            'pris_par_nom' => ['required', 'string', 'max:255'],
            'pris_par_prenom' => ['required', 'string', 'max:255'],
            'pris_par_role' => ['required', Rule::in(RoleUtilisateur::valeurs())],
        ], [], [
            'pris_par_im' => 'IM',
            'pris_par_nom' => 'nom',
            'pris_par_prenom' => 'prénom',
            'pris_par_role' => 'rôle',
        ]);

        $rolePris = RoleUtilisateur::from($donnees['pris_par_role']);

        $identite = sprintf(
            'Prise en charge par IM %s — %s %s (%s).',
            $donnees['pris_par_im'],
            mb_strtoupper($donnees['pris_par_nom']),
            $donnees['pris_par_prenom'],
            $rolePris->label(),
        );

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $utilisateur->id,
            'type_action' => 'prise_en_charge',
            'ancien_statut' => $dossier->statut->value,
            'nouveau_statut' => $dossier->statut->value,
            'observation' => $identite,
        ]);

        // Mettre à jour le responsable si non défini ou si l'utilisateur veut s'en charger
        if ((int) $dossier->responsable_id !== (int) $utilisateur->id && $utilisateur->peutChangerStatut($dossier, $dossier->statut)) {
            $ancienResponsable = $dossier->responsable_id;
            $dossier->update(['responsable_id' => $utilisateur->id]);

            Historique::create([
                'dossier_id' => $dossier->id,
                'utilisateur_id' => $utilisateur->id,
                'type_action' => 'attribution_responsable',
                'ancien_statut' => $dossier->statut->value,
                'nouveau_statut' => $dossier->statut->value,
                'observation' => $ancienResponsable
                    ? 'Responsable réattribué lors de la prise en charge'
                    : 'Attribution comme responsable à la prise en charge',
            ]);
        }

        return back()->with(
            'succes',
            'Le dossier a été pris en charge par '.$donnees['pris_par_prenom'].' '.mb_strtoupper($donnees['pris_par_nom']).'.'
        );
    }

    /**
     * Réception ou retrait d'une pièce exigée.
     */
    public function basculerPiece(Request $request, Dossier $dossier): RedirectResponse|JsonResponse
    {
        $this->verifierDivision($dossier->division_id);

        $donnees = $request->validate([
            'type_piece_id' => ['required', 'exists:types_pieces,id'],
        ]);

        $exigee = $dossier->piecesRequises()->where('type_piece_id', $donnees['type_piece_id'])->exists();
        abort_unless($exigee, 422, 'Cette pièce n\'est pas exigée pour ce type de dossier.');

        $existante = $dossier->piecesRecues()->where('type_piece_id', $donnees['type_piece_id'])->first();

        if ($existante !== null) {
            $existante->delete();
            $action = 'retrait_piece';
            $message = 'La pièce a été retirée du dossier.';
        } else {
            $dossier->piecesRecues()->create(['type_piece_id' => $donnees['type_piece_id']]);
            $action = 'ajout_piece';
            $message = 'La pièce a été réceptionnée.';
        }

        $typePiece = $dossier->piecesRequises()->with('typePiece')->where('type_piece_id', $donnees['type_piece_id'])->first()->typePiece;

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $request->user()->id,
            'type_action' => $action,
            'ancien_statut' => $dossier->statut->value,
            'nouveau_statut' => $dossier->statut->value,
            'observation' => 'Pièce : '.$typePiece->libelle,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'recue' => $existante === null,
                'message' => $message,
                'completude' => $dossier->fresh()->completude(),
            ]);
        }

        return back()->with('succes', $message);
    }

    /**
     * Valide la complétude des pièces (dossiers rejetés/incomplets) : remet le dossier
     * en circuit (En traitement) pour qu'il puisse être pris en charge.
     */
    public function validerPieces(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->verifierDivision($dossier->division_id);

        if (! in_array($dossier->statut, [StatutDossier::Rejete, StatutDossier::Incomplet], true)) {
            return back()->with('erreur', 'Cette action n\'est disponible que pour un dossier rejeté ou incomplet.');
        }

        if (! $dossier->estComplet()) {
            $c = $dossier->completude();
            return back()->with('erreur', 'Toutes les pièces requises ne sont pas encore reçues ('.$c['recues'].'/'.$c['exigees'].').');
        }

        $ancien = $dossier->statut;
        $nouveau = StatutDossier::EnTraitement;
        $utilisateur = $request->user();

        $dossier->update([
            'statut' => $nouveau,
            'modifie_le' => now(),
        ]);

        Historique::create([
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $utilisateur?->id,
            'type_action' => 'changement_statut',
            'ancien_statut' => $ancien->value,
            'nouveau_statut' => $nouveau->value,
            'observation' => 'Dossier régularisé (toutes les pièces reçues) : validation OK, remis en traitement pour prise en charge.',
        ]);

        return back()->with('succes', 'Dossier validé : toutes les pièces sont reçues. Il peut maintenant être pris en charge.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function valider(Request $request): array
    {
        $donnees = $request->validate([
            'bordereau_recu_id' => ['required', 'exists:bordereaux_recus,id'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'agent_im' => ['nullable', 'string', 'max:30', 'required_without:agent_id'],
            'agent_nom' => ['nullable', 'string', 'max:100', 'required_without:agent_id'],
            'agent_prenoms' => ['nullable', 'string', 'max:150'],
            'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'responsable_id' => ['nullable', 'exists:utilisateurs,id'],
            'pole' => ['nullable', 'in:'.implode(',', PoleDossier::valeurs())],
            'passe_par_visa' => ['nullable', 'boolean'],
            'reference_acte' => ['nullable', 'string', 'max:60'],
            'date_acte' => ['nullable', 'date'],
            'num_visa' => ['nullable', 'string', 'max:20'],
            'date_visa' => ['nullable', 'date'],
            'observation' => ['nullable', 'string'],
        ], [
            'agent_im.required_without' => 'Choisissez un agent dans la liste ou saisissez son IM.',
            'agent_nom.required_without' => 'Choisissez un agent dans la liste ou saisissez son nom.',
        ], [
            'bordereau_recu_id' => 'bordereau',
            'agent_id' => 'agent',
            'type_dossier_id' => 'type de dossier',
            'responsable_id' => 'responsable',
            'pole' => 'pôle',
            'date_visa' => 'date du visa',
            'date_acte' => "date de l'acte",
            'num_visa' => 'numéro de visa',
            'reference_acte' => "référence de l'acte",
        ]);

        $bordereau = BordereauRecu::find($donnees['bordereau_recu_id']);
        $type = TypeDossier::find($donnees['type_dossier_id']);

        abort_if($bordereau === null || $type === null, 422);
        abort_unless((int) $bordereau->division_id === (int) $type->division_id, 422,
            'Le type de dossier n\'appartient pas à la division du bordereau.');

        if (empty($donnees['responsable_id'])) {
            $donnees['responsable_id'] = $request->user()->id;
        }

        $donnees['agent_id'] = $this->agentDepuisSaisie($donnees)->id;
        unset($donnees['agent_im'], $donnees['agent_nom'], $donnees['agent_prenoms']);

        $donnees['pole'] = $donnees['pole'] ?? $this->poleDeLaDivision($bordereau->division_id)->value;
        $donnees['passe_par_visa'] = $request->boolean('passe_par_visa')
            || $donnees['pole'] === PoleDossier::Visa->value;
        $donnees['date_visa'] = $donnees['date_visa'] ?? null;
        $donnees['date_acte'] = $donnees['date_acte'] ?? null;
        $donnees['num_visa'] = $donnees['num_visa'] ?? null;
        $donnees['reference_acte'] = $donnees['reference_acte'] ?? null;

        return $donnees;
    }

    protected function typesDossiersVisibles()
    {
        return $this->appliquerPortee(TypeDossier::query())->with('division')->orderBy('code')->get();
    }

    protected function bordereauxDisponibles()
    {
        return $this->appliquerPortee(BordereauRecu::query())
            ->with('serviceOrigine')
            ->orderByDesc('date_arrivee')
            ->limit(200)
            ->get()
            ->map(fn (BordereauRecu $bordereau) => [
                'id' => $bordereau->id,
                'division_id' => $bordereau->division_id,
                'label' => sprintf(
                    'Arrivée n° %s du %s — %s (réf. %s)',
                    $bordereau->num_arrivee,
                    $bordereau->date_arrivee->format('d/m/Y'),
                    $bordereau->serviceOrigine?->nom,
                    $bordereau->reference
                ),
            ]);
    }

    protected function dernierBordereauDisponible(): ?BordereauRecu
    {
        return $this->appliquerPortee(BordereauRecu::query())->latest('date_arrivee')->first();
    }

    /**
     * Les agents sont un référentiel global (IM, nom, prénoms).
     */
    protected function agentsDisponibles()
    {
        return Agent::orderBy('nom')->get();
    }

    /**
     * Utilisateurs pouvant être désignés responsables d'un dossier.
     */
    protected function responsablesDisponibles(?int $divisionId)
    {
        return $this->appliquerPortee(User::query())
            ->where('role', '!=', RoleUtilisateur::Administrateur->value)
            ->when($divisionId !== null, fn ($query) => $query->where(fn ($sous) => $sous->whereHas('division', fn ($q) => $q->where('id', $divisionId))->orWhereNull('division_id')))
            ->orderBy('nom')
            ->get();
    }
}
