<?php

namespace App\Http\Controllers;

use App\Enums\StatutDossier;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Afficher le formulaire du profil de l'utilisateur.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'statistiques' => [
                'dossiers' => $user->dossiersResponsables()->count(),
                'archives' => $user->dossiersResponsables()
                    ->where('statut', StatutDossier::Archive->value)
                    ->count(),
                'actions' => $user->historiques()->count(),
            ],
        ]);
    }

    /**
     * Mettre à jour les informations du profil.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // La photo est traitée à part : ce n'est pas une simple colonne à
        // remplir, il faut aussi gérer le fichier sur le disque.
        $user->fill($request->safe()->except(['photo', 'supprimer_photo']));

        if ($request->hasFile('photo')) {
            $this->supprimerFichierPhoto($user);
            $user->photo = $request->file('photo')->store('photos', 'public');
        } elseif ($request->boolean('supprimer_photo')) {
            $this->supprimerFichierPhoto($user);
            $user->photo = null;
        }

        if ($user->isDirty('courriel')) {
            $user->courriel_verifie_le = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Supprimer le compte de l'utilisateur.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'mot_de_passe' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->supprimerFichierPhoto($user);

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Retirer le fichier de la photo de profil du disque.
     */
    private function supprimerFichierPhoto(User $user): void
    {
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
    }
}
