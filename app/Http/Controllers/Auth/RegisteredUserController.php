<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Afficher la page d'inscription.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Traiter une demande d'inscription.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'courriel' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'mot_de_passe' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $utilisateur = User::create([
            'nom' => $request->nom,
            'courriel' => $request->courriel,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
        ]);

        event(new Registered($utilisateur));

        Auth::login($utilisateur);

        return redirect(route('dashboard', absolute: false));
    }
}
