<?php

namespace App\Http\Controllers;

use App\Models\PieceRequise;
use App\Models\TypeDossier;
use App\Models\TypePiece;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PieceRequiseController extends Controller
{
    public function index(Request $request): View
    {
        $typesDossiers = TypeDossier::with('division')->orderBy('division_id')->orderBy('code')->get();

        $typeDossier = $typesDossiers->firstWhere('id', $request->integer('type_dossier_id') ?: null)
            ?? $typesDossiers->first();

        abort_if($typeDossier === null, 404, 'Aucun type de dossier n\'est défini.');

        $typeDossier->load('piecesRequises.typePiece');

        $typesPiecesDisponibles = TypePiece::whereDoesntHave('piecesRequises', fn ($q) => $q->where('type_dossier_id', $typeDossier->id))
            ->orderBy('libelle')
            ->get();

        return view('parametrage.pieces-requises.index', compact('typeDossier', 'typesDossiers', 'typesPiecesDisponibles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'type_dossier_id' => ['required', 'exists:types_dossiers,id'],
            'type_piece_id' => ['required', 'exists:types_pieces,id'],
            'nb_exemplaires' => ['required', 'integer', 'min:1', 'max:99'],
        ], [], ['nb_exemplaires' => 'nombre d\'exemplaires']);

        $existant = PieceRequise::where('type_dossier_id', $donnees['type_dossier_id'])
            ->where('type_piece_id', $donnees['type_piece_id'])
            ->first();

        if ($existant !== null) {
            return back()->with('erreur', 'Cette pièce est déjà exigée pour ce type de dossier.');
        }

        PieceRequise::create($donnees);

        return redirect()
            ->route('pieces-requises.index', ['type_dossier_id' => $donnees['type_dossier_id']])
            ->with('succes', 'La pièce a été ajoutée aux exigences.');
    }

    public function update(Request $request, PieceRequise $pieceRequise): RedirectResponse
    {
        $donnees = $request->validate([
            'nb_exemplaires' => ['required', 'integer', 'min:1', 'max:99'],
        ], [], ['nb_exemplaires' => 'nombre d\'exemplaires']);

        $pieceRequise->update($donnees);

        return redirect()
            ->route('pieces-requises.index', ['type_dossier_id' => $pieceRequise->type_dossier_id])
            ->with('succes', 'Le nombre d\'exemplaires a été mis à jour.');
    }

    public function destroy(PieceRequise $pieceRequise): RedirectResponse
    {
        $typeDossierId = $pieceRequise->type_dossier_id;
        $pieceRequise->delete();

        return redirect()
            ->route('pieces-requises.index', ['type_dossier_id' => $typeDossierId])
            ->with('succes', 'La pièce n\'est plus exigée pour ce type de dossier.');
    }
}
