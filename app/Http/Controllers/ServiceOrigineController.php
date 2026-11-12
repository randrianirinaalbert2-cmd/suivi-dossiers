<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrigine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceOrigineController extends Controller
{
    public function index(Request $request): View
    {
        $services = ServiceOrigine::withCount('bordereauxRecus')
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('parametrage.services-origine.index', compact('services'));
    }

    public function create(): View
    {
        return view('parametrage.services-origine.form', ['service' => new ServiceOrigine]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150', 'unique:services_origine,nom'],
        ]);

        ServiceOrigine::create($donnees);

        return redirect()->route('services-origine.index')->with('succes', 'Le service d\'origine a été créé.');
    }

    public function edit(ServiceOrigine $service): View
    {
        return view('parametrage.services-origine.form', compact('service'));
    }

    public function update(Request $request, ServiceOrigine $service): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150', 'unique:services_origine,nom,'.$service->id],
        ]);

        $service->update($donnees);

        return redirect()->route('services-origine.index')->with('succes', 'Le service d\'origine a été mis à jour.');
    }

    public function destroy(ServiceOrigine $service): RedirectResponse
    {
        if ($service->bordereauxRecus()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : ce service est utilisé par des bordereaux reçus.');
        }

        $service->delete();

        return redirect()->route('services-origine.index')->with('succes', 'Le service d\'origine a été supprimé.');
    }
}
