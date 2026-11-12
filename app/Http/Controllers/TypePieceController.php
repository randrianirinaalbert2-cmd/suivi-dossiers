<?php

namespace App\Http\Controllers;

use App\Models\TypePiece;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypePieceController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));

        $typesPieces = TypePiece::withCount('piecesRequises', 'piecesRecues')
            ->when($recherche !== '', fn ($query) => $query->where('libelle', 'like', "%{$recherche}%"))
            ->orderBy('libelle')
            ->paginate(10)
            ->withQueryString();

        return view('parametrage.types-pieces.index', compact('typesPieces', 'recherche'));
    }

    public function create(): View
    {
        return view('parametrage.types-pieces.form', ['typePiece' => new TypePiece]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'libelle' => ['required', 'string', 'max:100', 'unique:types_pieces,libelle'],
        ]);

        TypePiece::create($donnees);

        return redirect()->route('types-pieces.index')->with('succes', 'Le type de pièce a été créé.');
    }

    public function edit(TypePiece $typePiece): View
    {
        return view('parametrage.types-pieces.form', compact('typePiece'));
    }

    public function update(Request $request, TypePiece $typePiece): RedirectResponse
    {
        $donnees = $request->validate([
            'libelle' => ['required', 'string', 'max:100', 'unique:types_pieces,libelle,'.$typePiece->id],
        ]);

        $typePiece->update($donnees);

        return redirect()->route('types-pieces.index')->with('succes', 'Le type de pièce a été mis à jour.');
    }

    public function destroy(TypePiece $typePiece): RedirectResponse
    {
        if ($typePiece->piecesRequises()->exists() || $typePiece->piecesRecues()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : ce type de pièce est encore utilisé.');
        }

        $typePiece->delete();

        return redirect()->route('types-pieces.index')->with('succes', 'Le type de pièce a été supprimé.');
    }
}
