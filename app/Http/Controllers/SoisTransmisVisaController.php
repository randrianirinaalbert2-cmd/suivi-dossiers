<?php

namespace App\Http\Controllers;

use App\Enums\PoleDossier;
use App\Enums\StatutDossier;
use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\BordereauRecu;
use App\Models\Dossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Notes « SOIT TRANSMIS » du Visa, établies à partir d'un bordereau reçu.
 *
 * Une note par type de dossier : tous les dossiers traités du même type
 * figurent dans la même note, adressée au contrôle financier.
 */
class SoitTransmisVisaController extends Controller
{
    use PorteeParDivision;

    public function __invoke(Request $request, BordereauRecu $bordereau): View|RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        $bordereau->loadMissing(['serviceOrigine', 'division']);

        // La note du Visa ne concerne que les bordereaux de la division Visa.
        abort_unless($bordereau->division?->poleNaturel() === PoleDossier::Visa, 404);

        // Seuls les dossiers dont le traitement est terminé sont transmis.
        $dossiers = $bordereau->dossiers()
            ->with(['agent', 'typeDossier'])
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

        $notes = $dossiers
            ->groupBy('type_dossier_id')
            ->map(fn (Collection $groupe) => [
                'intitule' => $this->intitule($groupe->first()->typeDossier?->libelle ?? ''),
                'agents' => $groupe->map(fn (Dossier $dossier) => [
                    'nom' => mb_strtoupper(trim($dossier->agent?->nom ?? '')),
                    'prenoms' => trim($dossier->agent?->prenoms ?? ''),
                    'im' => $this->formaterIm($dossier->agent?->im),
                ])->values()->all(),
            ])
            ->values()
            ->all();

        return view('bordereaux.soit-transmis-visa', [
            'bordereau' => $bordereau,
            'notes' => $notes,
            'destinataires' => config('soit_transmis.destinataires'),
            'destinataireDefaut' => config('soit_transmis.destinataire_par_defaut'),
            'annee' => now()->year,
        ]);
    }

    /**
     * Phrase « Projet d'arrêté portant … » propre au type de dossier.
     */
    protected function intitule(string $libelle): string
    {
        $minuscules = mb_strtolower($libelle);

        foreach (config('soit_transmis.intitules', []) as $regle) {
            if (str_contains($minuscules, mb_strtolower($regle['mot']))) {
                return $regle['intitule'];
            }
        }

        return trim(config('soit_transmis.intitule_par_defaut').' '.$libelle);
    }

    /**
     * IM écrit avec un point des milliers, comme sur le modèle (415830 → 415.830).
     */
    protected function formaterIm(?string $im): string
    {
        $im = trim((string) $im);

        if (preg_match('/^\d{4,}$/', $im) !== 1) {
            return $im;
        }

        return strrev(implode('.', str_split(strrev($im), 3)));
    }
}
