<?php

namespace App\Http\Controllers;

use App\Enums\FamilleMouvement;
use App\Http\Controllers\Concerns\PorteeParDivision;
use App\Models\Division;
use App\Models\TypeDossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeDossierController extends Controller
{
    use PorteeParDivision;

    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));
        $divisionId = $request->integer('division_id') ?: null;
        $famille = $request->query('famille') ?: null;

        $typesDossiers = $this->appliquerPortee(TypeDossier::query())
            ->with('division')
            ->withCount(['piecesRequises', 'dossiers'])
            ->when($divisionId !== null, fn ($query) => $query->where('division_id', $divisionId))
            ->when($famille !== null, fn ($query) => $query->where('famille', $famille))
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($sous) use ($recherche) {
                    $sous->where('code', 'like', "%{$recherche}%")
                        ->orWhere('libelle', 'like', "%{$recherche}%");
                });
            })
            ->orderBy('division_id')
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        $divisions = Division::orderBy('nom')->get();

        return view('parametrage.types-dossiers.index', [
            'typesDossiers' => $typesDossiers,
            'divisions' => $divisions,
            'familles' => FamilleMouvement::cases(),
            'recherche' => $recherche,
            'divisionId' => $divisionId,
            'familleId' => $famille,
        ]);
    }

    public function create(Request $request): View
    {
        return view('parametrage.types-dossiers.form', [
            'typeDossier' => new TypeDossier(['division_id' => $request->integer('division_id') ?: null]),
            'divisions' => $this->divisionsVisibles(),
            'familles' => FamilleMouvement::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'code' => ['required', 'string', 'max:10', 'unique:types_dossiers,code'],
            'libelle' => ['required', 'string', 'max:100'],
            'famille' => ['nullable', 'in:'.implode(',', FamilleMouvement::valeurs())],
        ], [], ['division_id' => 'division', 'code' => 'code', 'libelle' => 'libellé', 'famille' => 'famille']);

        $this->verifierDivision((int) $donnees['division_id']);
        TypeDossier::create($donnees);

        return redirect()->route('types-dossiers.index')->with('succes', 'Le type de dossier a été créé.');
    }

    public function edit(TypeDossier $typeDossier): View
    {
        $this->verifierDivision($typeDossier->division_id);

        return view('parametrage.types-dossiers.form', [
            'typeDossier' => $typeDossier,
            'divisions' => $this->divisionsVisibles(),
            'familles' => FamilleMouvement::cases(),
        ]);
    }

    public function update(Request $request, TypeDossier $typeDossier): RedirectResponse
    {
        $this->verifierDivision($typeDossier->division_id);

        $donnees = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'code' => ['required', 'string', 'max:10', 'unique:types_dossiers,code,'.$typeDossier->id],
            'libelle' => ['required', 'string', 'max:100'],
            'famille' => ['nullable', 'in:'.implode(',', FamilleMouvement::valeurs())],
        ], [], ['division_id' => 'division', 'code' => 'code', 'libelle' => 'libellé', 'famille' => 'famille']);

        $this->verifierDivision((int) $donnees['division_id']);
        $typeDossier->update($donnees);

        return redirect()->route('types-dossiers.index')->with('succes', 'Le type de dossier a été mis à jour.');
    }

    public function destroy(TypeDossier $typeDossier): RedirectResponse
    {
        $this->verifierDivision($typeDossier->division_id);

        if ($typeDossier->dossiers()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : des dossiers utilisent déjà ce type.');
        }

        $typeDossier->delete();

        return redirect()->route('types-dossiers.index')->with('succes', 'Le type de dossier a été supprimé.');
    }
}
