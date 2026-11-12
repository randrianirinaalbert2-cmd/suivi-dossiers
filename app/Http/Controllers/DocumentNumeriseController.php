<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\BordereauRecu;
use App\Models\DocumentNumerise;
use App\Models\Dossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentNumeriseController extends Controller
{
    use PorteeParDivision;

    /**
     * Numérisation d'un bordereau reçu.
     */
    public function stockerBordereau(Request $request, BordereauRecu $bordereau): RedirectResponse
    {
        $this->verifierDivision($bordereau->division_id);

        return $this->enregistrer($request, $bordereau, DocumentNumerise::TYPE_BORDEREAU, 'bordereaux.show', $bordereau);
    }

    /**
     * Numérisation d'un document rattaché à un dossier.
     */
    public function stockerDossier(Request $request, Dossier $dossier): RedirectResponse
    {
        $this->verifierDivision($dossier->division_id);

        return $this->enregistrer($request, $dossier, DocumentNumerise::TYPE_DOSSIER, 'dossiers.show', $dossier);
    }

    public function supprimer(Request $request, DocumentNumerise $document): RedirectResponse
    {
        $parent = $document->dossier ?? $document->bordereauRecu;
        abort_if($parent !== null && ! $this->utilisateurCourant()?->voitDivision($parent->division_id), 403);

        $retour = $document->dossier
            ? ['dossiers.show', $document->dossier]
            : ['bordereaux.show', $document->bordereauRecu];

        Storage::disk('public')->delete($document->chemin_fichier);
        $document->delete();

        return redirect()
            ->route($retour[0], $retour[1])
            ->with('succes', 'Le document numérique a été supprimé.');
    }

    public function telecharger(DocumentNumerise $document): View|StreamedResponse
    {
        $parent = $document->dossier ?? $document->bordereauRecu;
        abort_if($parent !== null && ! $this->utilisateurCourant()?->voitDivision($parent->division_id), 403);

        abort_unless(Storage::disk('public')->exists($document->chemin_fichier), 404);

        return Storage::disk('public')->download($document->chemin_fichier, $document->nom_fichier);
    }

    protected function enregistrer(
        Request $request,
        BordereauRecu|Dossier $parent,
        string $typeDocument,
        string $nomRetour,
        BordereauRecu|Dossier $objetRetour,
    ): RedirectResponse {
        $request->validate([
            'fichier' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'fichier.required' => 'Sélectionnez le fichier à numériser.',
            'fichier.mimes' => 'Formats acceptés : PDF, JPG, PNG, WEBP.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $fichier = $request->file('fichier');
        $empreinte = hash_file('sha256', $fichier->getRealPath());

        $dejaConnu = DocumentNumerise::where('empreinte_fichier', $empreinte)->exists();

        if ($dejaConnu) {
            return redirect()
                ->route($nomRetour, $objetRetour)
                ->with('erreur', 'Ce document est déjà enregistré dans le système (empreinte identique).');
        }

        $dossier = $parent instanceof Dossier ? $parent : null;
        $bordereau = $parent instanceof BordereauRecu ? $parent : null;

        $dossierId = $dossier?->id;
        $bordereauId = $bordereau?->id;

        $repertoire = 'numerisation/'.($dossier !== null ? 'dossiers/'.$dossierId : 'bordereaux/'.$bordereauId);
        $chemin = $fichier->store($repertoire, 'public');

        DocumentNumerise::create([
            'bordereau_recu_id' => $bordereauId,
            'dossier_id' => $dossierId,
            'utilisateur_id' => $request->user()->id,
            'type_document' => $typeDocument,
            'nom_fichier' => $fichier->getClientOriginalName(),
            'chemin_fichier' => $chemin,
            'empreinte_fichier' => $empreinte,
            'taille_fichier' => $fichier->getSize(),
            'date_numerisation' => now(),
        ]);

        if ($dossier instanceof Dossier) {
            $dossier->historiques()->create([
                'utilisateur_id' => $request->user()->id,
                'type_action' => 'numerisation',
                'ancien_statut' => $dossier->statut->value,
                'nouveau_statut' => $dossier->statut->value,
                'observation' => 'Ajout du document : '.$fichier->getClientOriginalName(),
            ]);
        }

        return redirect()
            ->route($nomRetour, $objetRetour)
            ->with('succes', 'Le document a été numérisé et enregistré (empreinte SHA-256 conservée).');
    }
}
