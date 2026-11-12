<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Afficher la page de réinitialisation du mot de passe.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Traiter la demande de nouveau mot de passe.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'courriel' => ['required', 'email'],
            'mot_de_passe' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // On tente de réinitialiser le mot de passe. En cas de succès, le mot de passe
        // de l'utilisateur est mis à jour dans la base de données. Sinon, on renvoie
        // l'utilisateur vers le formulaire avec le message d'erreur correspondant.
        $statut = Password::reset(
            [
                'courriel' => $request->courriel,
                'password' => $request->mot_de_passe,
                'password_confirmation' => $request->mot_de_passe_confirmation,
                'token' => $request->token,
            ],
            function (User $utilisateur) use ($request) {
                $utilisateur->forceFill([
                    'mot_de_passe' => Hash::make($request->mot_de_passe),
                    'jeton_souvenir' => Str::random(60),
                ])->save();

                event(new PasswordReset($utilisateur));
            }
        );

        return $statut == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($statut))
                    : back()->withInput($request->only('courriel'))
                        ->withErrors(['courriel' => __($statut)]);
    }
}
