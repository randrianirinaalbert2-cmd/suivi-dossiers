<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));

        $agents = Agent::withCount('dossiers')
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($sous) use ($recherche) {
                    $sous->where('im', 'like', "%{$recherche}%")
                        ->orWhere('nom', 'like', "%{$recherche}%")
                        ->orWhere('prenoms', 'like', "%{$recherche}%");
                });
            })
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('parametrage.agents.index', compact('agents', 'recherche'));
    }

    public function create(): View
    {
        return view('parametrage.agents.form', ['agent' => new Agent]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'im' => ['required', 'string', 'max:10', 'unique:agents,im'],
            'nom' => ['required', 'string', 'max:100'],
            'prenoms' => ['nullable', 'string', 'max:150'],
        ]);

        Agent::create($donnees);

        return redirect()->route('agents.index')->with('succes', 'L\'agent a été créé.');
    }

    public function edit(Agent $agent): View
    {
        return view('parametrage.agents.form', compact('agent'));
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $donnees = $request->validate([
            'im' => ['required', 'string', 'max:10', 'unique:agents,im,'.$agent->id],
            'nom' => ['required', 'string', 'max:100'],
            'prenoms' => ['nullable', 'string', 'max:150'],
        ]);

        $agent->update($donnees);

        return redirect()->route('agents.index')->with('succes', 'L\'agent a été mis à jour.');
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        if ($agent->dossiers()->exists() || $agent->lignesBordereau()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : cet agent est référencé dans des dossiers ou des bordereaux.');
        }

        $agent->delete();

        return redirect()->route('agents.index')->with('succes', 'L\'agent a été supprimé.');
    }
}
