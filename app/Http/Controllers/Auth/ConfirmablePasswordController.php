<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Afficher la page de confirmation du mot de passe.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirmer le mot de passe de l'utilisateur.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'courriel' => $request->user()->courriel,
            'password' => $request->mot_de_passe,
        ])) {
            throw ValidationException::withMessages([
                'mot_de_passe' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
