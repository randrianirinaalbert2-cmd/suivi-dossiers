<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestreindreRole
{
    /**
     * Restreint l'accès à une ou plusieurs valeurs de rôle.
     *
     * Exemple : ->middleware('role:administrateur')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = $request->user();

        $autorisé = $utilisateur !== null
            && $utilisateur->role !== null
            && in_array($utilisateur->role->value, $roles, true);

        if (! $autorisé) {
            abort(403, 'Accès refusé : votre rôle ne permet pas d\'accéder à cette page.');
        }

        return $next($request);
    }
}
