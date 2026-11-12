<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = trim((string) $request->query('q', ''));

        $utilisateurs = User::with('division')
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($sous) use ($recherche) {
                    $sous->where('nom', 'like', "%{$recherche}%")
                        ->orWhere('courriel', 'like', "%{$recherche}%");
                });
            })
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('parametrage.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'recherche' => $recherche,
            'roles' => RoleUtilisateur::cases(),
            'divisions' => Division::orderBy('nom')->get(),
        ]);
    }

    public function create(): View
    {
        return view('parametrage.utilisateurs.form', [
            'utilisateur' => new User,
            'roles' => RoleUtilisateur::cases(),
            'divisions' => Division::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->valider($request);

        $utilisateur = new User($donnees);
        $utilisateur->courriel_verifie_le = now();
        $utilisateur->save();

        return redirect()->route('utilisateurs.index')->with('succes', 'L\'utilisateur a été créé.');
    }

    public function edit(User $user): View
    {
        return view('parametrage.utilisateurs.form', [
            'utilisateur' => $user,
            'roles' => RoleUtilisateur::cases(),
            'divisions' => Division::orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $donnees = $this->valider($request, $user);

        if (empty($donnees['mot_de_passe'])) {
            unset($donnees['mot_de_passe']);
        }

        $utilisateur = $user->fill($donnees);

        if ($utilisateur->isDirty('courriel')) {
            $utilisateur->courriel_verifie_le = now();
        }

        $utilisateur->save();

        return redirect()->route('utilisateurs.index')->with('succes', 'L\'utilisateur a été mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('erreur', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->dossiersResponsables()->exists()) {
            return back()->with('erreur', 'Impossible de supprimer : cet utilisateur est responsable de dossiers.');
        }

        $user->delete();

        return redirect()->route('utilisateurs.index')->with('succes', 'L\'utilisateur a été supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function valider(Request $request, ?User $utilisateur = null): array
    {
        $ignore = $utilisateur?->id;

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'courriel' => [
                'required', 'string', 'email', 'max:255', 'lowercase',
                Rule::unique('utilisateurs', 'courriel')->ignore($ignore),
            ],
            'role' => ['required', Rule::in(RoleUtilisateur::valeurs())],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'mot_de_passe' => [$utilisateur === null ? 'required' : 'nullable', 'string', 'min:8'],
        ], [], [
            'nom' => 'nom',
            'courriel' => 'adresse courriel',
            'role' => 'rôle',
            'division_id' => 'division',
            'mot_de_passe' => 'mot de passe',
        ]);

        $role = RoleUtilisateur::from($donnees['role']);

        if ($role !== RoleUtilisateur::Administrateur && empty($donnees['division_id'])) {
            throw ValidationException::withMessages([
                'division_id' => 'La division est obligatoire pour les rôles chef de division et responsable.',
            ]);
        }

        if ($role === RoleUtilisateur::Administrateur) {
            $donnees['division_id'] = null;
        }

        return $donnees;
    }
}
