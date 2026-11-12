<?php

namespace App\Http\Controllers;

use App\Enums\PoleDossier;
use App\Enums\StatutDossier;
use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\Dossier;
use App\Models\TypeDossier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    use PorteeParDivision;

    public function dossiers(Request $request): View
    {
        $filtres = $request->only(['du', 'au', 'pole', 'division_id', 'statut']);

        // Un membre de la division Solde ne voit que les dossiers de son
        // pôle ; l'administrateur et le chef de service voient tout.
        $poles = $this->polesAccessibles();
        $voitVisa = $poles === null || in_array(PoleDossier::Visa->value, $poles, true);
        $voitSolde = $poles === null || in_array(PoleDossier::Solde->value, $poles, true);

        $query = $this->limiterAuxPoles(
            $this->appliquerPortee(Dossier::query()),
            $poles
        )->with(['typeDossier', 'division']);

        if (! empty($filtres['du'])) {
            $query->whereDate('cree_le', '>=', $filtres['du']);
        }

        if (! empty($filtres['au'])) {
            $query->whereDate('cree_le', '<=', $filtres['au']);
        }

        if (! empty($filtres['pole'])) {
            $query->where('pole', $filtres['pole']);
        }

        if (! empty($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (! empty($filtres['division_id']) && $this->voitToutesLesDivisions()) {
            $query->where('division_id', $filtres['division_id']);
        }

        $base = clone $query;

        $stats = (clone $base)
            ->selectRaw('type_dossier_id, COUNT(*) as total')
            ->groupBy('type_dossier_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($ligne) {
                $type = TypeDossier::find($ligne->type_dossier_id);
                return [
                    'type' => $type,
                    'code' => $type?->code,
                    'libelle' => $type?->libelle,
                    'division' => $type?->division?->nom,
                    'total' => (int) $ligne->total,
                ];
            });

        $totalGlobal = $stats->sum('total');

        $parPole = [];
        foreach (PoleDossier::cases() as $pole) {
            $parPole[$pole->value] = (clone $base)->where('pole', $pole->value)->count();
        }
        $parPole['non_defini'] = (clone $base)->whereNull('pole')->count();

        $resume = [
            'en_instance' => (clone $base)->whereIn('statut', [
                StatutDossier::Recu->value,
                StatutDossier::EnTraitement->value,
                StatutDossier::Incomplet->value,
            ])->count(),
            'rejetes' => (clone $base)->where('statut', StatutDossier::Rejete->value)->count(),
            'finalises' => (clone $base)->whereIn('statut', [
                StatutDossier::Traite->value,
                StatutDossier::Verifie->value,
                StatutDossier::Transmis->value,
                StatutDossier::Archive->value,
            ])->count(),
        ];

        return view('statistiques.dossiers', [
            'stats' => $stats,
            'totalGlobal' => $totalGlobal,
            'parPole' => $parPole,
            'resume' => $resume,
            'filtres' => $filtres,
            'poles' => collect(PoleDossier::cases())
                ->filter(fn (PoleDossier $pole) => $poles === null || in_array($pole->value, $poles, true))
                ->values(),
            'voitVisa' => $voitVisa,
            'voitSolde' => $voitSolde,
            'statuts' => StatutDossier::cases(),
            'divisions' => $this->divisionsVisibles(),
        ]);
    }
}
