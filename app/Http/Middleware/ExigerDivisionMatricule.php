<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigerDivisionMatricule
{
    /**
     * Réserve les bordereaux de création à la division qui tient la
     * matricule des agents et à l'administrateur.
     *
     * Le bordereau de création liste les IM (matricules) des agents : seule
     * la division qui crée la matricule peut l'établir. La division Visa
     * n'établit pas de matricule, donc pas de bordereau de création.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        abort_unless(
            $utilisateur !== null && $utilisateur->peutGererBordereauxDeCreation(),
            403,
            'Accès refusé : les bordereaux de création (porteurs de la matricule des agents) relèvent de la division de la Solde.'
        );

        return $next($request);
    }
}
