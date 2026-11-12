<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(Request $request): View
    {
        $divisions = Division::withCount(['typesDossiers', 'utilisateurs', 'dossiers'])
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('parametrage.divisions.index', compact('divisions'));
    }

    public function create(): View
    {
        return view('parametrage.divisions.form', ['division' => new Division]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:50', 'unique:divisions,nom'],
        ]);

        Division::create($donnees);

        return redirect()->route('divisions.index')->with('succes', 'La division a été créée.');
    }

    public function edit(Division $division): View
    {
        return view('parametrage.divisions.form', compact('division'));
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:50', 'unique:divisions,nom,'.$division->id],
        ]);

        $division->update($donnees);

        return redirect()->route('divisions.index')->with('succes', 'La division a été mise à jour.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        if ($division->dossiers()->exists() || $division->utilisateurs()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : cette division est encore utilisée.');
        }

        $division->delete();

        return redirect()->route('divisions.index')->with('succes', 'La division a été supprimée.');
    }
}
