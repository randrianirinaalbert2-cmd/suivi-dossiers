<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmpecherMiseEnCache
{
    /**
     * Empêche le navigateur de conserver les pages dans son cache.
     *
     * Sans ces en-têtes, le bouton « retour » peut réafficher une page
     * connectée (tableau de bord, dossiers, bordereaux...) après la
     * déconnexion, alors même que la session a été invalidée côté serveur.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $reponse = $next($request);

        $reponse->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $reponse->headers->set('Pragma', 'no-cache');
        $reponse->headers->set('Expires', '0');

        return $reponse;
    }
}
