<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::withCount('bordereauxCreation')
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('parametrage.destinations.index', compact('destinations'));
    }

    public function create(): View
    {
        return view('parametrage.destinations.form', ['destination' => new Destination]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150', 'unique:destinations,nom'],
        ]);

        Destination::create($donnees);

        return redirect()->route('destinations.index')->with('succes', 'La destination a été créée.');
    }

    public function edit(Destination $destination): View
    {
        return view('parametrage.destinations.form', compact('destination'));
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150', 'unique:destinations,nom,'.$destination->id],
        ]);

        $destination->update($donnees);

        return redirect()->route('destinations.index')->with('succes', 'La destination a été mise à jour.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        if ($destination->bordereauxCreation()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : cette destination est utilisée.');
        }

        $destination->delete();

        return redirect()->route('destinations.index')->with('succes', 'La destination a été supprimée.');
    }
}
