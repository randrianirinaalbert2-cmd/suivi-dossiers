<?php

namespace App\Http\Controllers;

use App\Models\Lieu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LieuController extends Controller
{
    public function index(Request $request): View
    {
        $lieux = Lieu::withCount('bordereauxCreation')
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('parametrage.lieux.index', compact('lieux'));
    }

    public function create(): View
    {
        return view('parametrage.lieux.form', ['lieu' => new Lieu]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:lieux,code'],
            'nom' => ['required', 'string', 'max:100'],
            'sigle' => ['required', 'string', 'max:3'],
        ]);

        Lieu::create($donnees);

        return redirect()->route('lieux.index')->with('succes', 'Le lieu a été créé.');
    }

    public function edit(Lieu $lieu): View
    {
        return view('parametrage.lieux.form', compact('lieu'));
    }

    public function update(Request $request, Lieu $lieu): RedirectResponse
    {
        $donnees = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:lieux,code,'.$lieu->id],
            'nom' => ['required', 'string', 'max:100'],
            'sigle' => ['required', 'string', 'max:3'],
        ]);

        $lieu->update($donnees);

        return redirect()->route('lieux.index')->with('succes', 'Le lieu a été mis à jour.');
    }

    public function destroy(Lieu $lieu): RedirectResponse
    {
        if ($lieu->bordereauxCreation()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : ce lieu est utilisé par des bordereaux de création.');
        }

        $lieu->delete();

        return redirect()->route('lieux.index')->with('succes', 'Le lieu a été supprimé.');
    }
}
