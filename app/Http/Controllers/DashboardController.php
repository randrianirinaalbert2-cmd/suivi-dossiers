<?php

namespace App\Http\Controllers;

use App\Enums\PoleDossier;
use App\Enums\StatutDossier;
use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\BordereauCreation;
use App\Models\BordereauRecu;
use App\Models\Dossier;
use App\Models\Historique;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use PorteeParDivision;

    public function __invoke(Request $request): View
    {
        // Pôles que l'utilisateur a le droit de consulter : un membre de la
        // division Solde ne doit jamais voir les indicateurs ni les alertes
        // du pôle Visa (et inversement). L'administrateur et le chef de
        // service (hiérarchique) voient les deux pôles.
        $poles = $this->polesAccessibles();
        $voitVisa = $poles === null || in_array(PoleDossier::Visa->value, $poles, true);
        $voitSolde = $poles === null || in_array(PoleDossier::Solde->value, $poles, true);

        $dossiers = $this->limiterAuxPoles(
            $this->appliquerPortee(Dossier::query()),
            $poles
        );
        $bordereaux = $this->appliquerPortee(BordereauRecu::query());

        $parStatut = [];
        foreach (StatutDossier::cases() as $statut) {
            $parStatut[$statut->value] = 0;
        }

        foreach ((clone $dossiers)->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->get() as $ligne) {
            $cle = $ligne->statut instanceof \BackedEnum ? $ligne->statut->value : (string) $ligne->statut;
            $parStatut[$cle] = (int) $ligne->total;
        }

        $total = array_sum($parStatut);
        $enCours = $parStatut[StatutDossier::Recu->value]
            + $parStatut[StatutDossier::EnTraitement->value]
            + $parStatut[StatutDossier::Traite->value]
            + $parStatut[StatutDossier::Verifie->value];

        $enRetard = $this->limiterAuxPoles((clone $dossiers), $poles)
            ->whereIn('statut', [StatutDossier::Recu->value, StatutDossier::EnTraitement->value])
            ->where(function ($q) {
                $q->where(function ($visa) {
                    $visa->where('pole', PoleDossier::Visa->value)
                        ->where('cree_le', '<=', now()->subHours(72));
                })->orWhere(function ($autre) {
                    $autre->where(function ($p) {
                        $p->whereNull('pole')->orWhere('pole', '!=', PoleDossier::Visa->value);
                    })->where('cree_le', '<=', now()->subDays(7));
                });
            })
            ->count();

        $auVisa = $voitVisa
            ? (clone $dossiers)->where('pole', PoleDossier::Visa->value)->count()
            : 0;
        $auSolde = $voitSolde
            ? (clone $dossiers)->where('pole', PoleDossier::Solde->value)->count()
            : 0;

        $dossiersRejetesNonPris = (clone $dossiers)
            ->with(['agent', 'division', 'responsable'])
            ->where('statut', StatutDossier::Rejete->value)
            ->where(function ($query) {
                $query->where('modifie_le', '<=', now()->subDays(2))
                    ->orWhere('cree_le', '<=', now()->subDays(2));
            })
            ->whereDoesntHave('historiques', function ($q) {
                $q->whereIn('type_action', ['prise_en_charge', 'attribution_responsable']);
            })
            ->orderByDesc('modifie_le')
            ->limit(10)
            ->get();

        $dossiersTraitesAujourdhui = (clone $dossiers)
            ->whereIn('statut', [StatutDossier::Traite->value, StatutDossier::Verifie->value, StatutDossier::Transmis->value])
            ->whereDate('modifie_le', now()->toDateString())
            ->count();

        $dossiersVisaEnRetard = $voitVisa
            ? (clone $dossiers)
                ->with(['agent', 'division', 'responsable'])
                ->where('pole', PoleDossier::Visa->value)
                ->whereIn('statut', [StatutDossier::Recu->value, StatutDossier::EnTraitement->value])
                ->where('cree_le', '<=', now()->subHours(72))
                ->orderBy('cree_le')
                ->limit(10)
                ->get()
            : collect();

        $dossiersRejetesIncomplets72h = (clone $dossiers)
            ->with(['agent', 'division', 'responsable'])
            ->whereIn('statut', [StatutDossier::Rejete->value, StatutDossier::Incomplet->value])
            ->where(function ($query) {
                $query->where('modifie_le', '<=', now()->subHours(72))
                    ->orWhere(function ($q) {
                        $q->whereNull('modifie_le')->where('cree_le', '<=', now()->subHours(72));
                    });
            })
            ->orderByRaw('COALESCE(modifie_le, cree_le) asc')
            ->limit(10)
            ->get();

        $rejetDefinitif = (clone $dossiers)->where('statut', StatutDossier::Rejete->value)->count();
        $enAttente = (clone $dossiers)->whereIn('statut', [StatutDossier::Recu->value, StatutDossier::EnTraitement->value])->count();

        // Les bordereaux de création portent la matricule des agents : ils ne
        // concernent que la division qui tient la matricule.
        $bordereauxCreation = $request->user()?->peutGererBordereauxDeCreation()
            ? $this->porteeMatricule(BordereauCreation::query())
                ->with(['lieu', 'destination', 'division'])
                ->where('annee', now()->year)
                ->orderByDesc('num_ordre')
                ->limit(6)
                ->get()
            : collect();

        return view('dashboard', [
            'total' => $total,
            'enCours' => $enCours,
            'auVisa' => $auVisa,
            'auSolde' => $auSolde,
            'voitVisa' => $voitVisa,
            'voitSolde' => $voitSolde,
            'incomplets' => $parStatut[StatutDossier::Incomplet->value],
            'enRetard' => $enRetard,
            'parStatut' => $parStatut,
            'statuts' => StatutDossier::cases(),
            'partEnCours' => $total > 0 ? round(($enCours / $total) * 100) : 0,
            'bordereauxDuMois' => (clone $bordereaux)->where('date_arrivee', '>=', now()->startOfMonth())->count(),
            'dossiersDuMois' => (clone $dossiers)->where('cree_le', '>=', now()->startOfMonth())->count(),
            'derniersDossiers' => (clone $dossiers)
                ->with(['agent', 'typeDossier', 'division', 'responsable'])
                ->latest('id')
                ->limit(8)
                ->get(),
            'derniersBordereaux' => (clone $bordereaux)
                ->with(['serviceOrigine', 'division'])
                ->latest('date_arrivee')
                ->limit(6)
                ->get(),
            'bordereauxCreation' => $bordereauxCreation,
            'dossiersRejetesNonPris' => $dossiersRejetesNonPris,
            'dossiersVisaEnRetard' => $dossiersVisaEnRetard,
            'dossiersRejetesIncomplets72h' => $dossiersRejetesIncomplets72h,
            'dossiersTraitesAujourdhui' => $dossiersTraitesAujourdhui,
            'rejetDefinitif' => $rejetDefinitif,
            'enAttente' => $enAttente,
        ]);
    }
}
