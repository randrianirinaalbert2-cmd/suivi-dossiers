<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Afficher la page de demande du lien de réinitialisation.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Traiter la demande du lien de réinitialisation.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'courriel' => ['required', 'email'],
        ]);

        // On envoie le lien de réinitialisation à cette adresse, puis on examine
        // la réponse pour afficher le bon message à l'utilisateur.
        $statut = Password::sendResetLink(
            $request->only('courriel')
        );

        return $statut == Password::RESET_LINK_SENT
                    ? back()->with('status', __($statut))
                    : back()->withInput($request->only('courriel'))
                        ->withErrors(['courriel' => __($statut)]);
    }
}
