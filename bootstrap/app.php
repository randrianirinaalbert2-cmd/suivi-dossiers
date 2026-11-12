<?php

use App\Http\Middleware\RestreindreRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// Alias de middleware employés dans routes/web.php :
//   - 'role'      : limite une route à certains rôles ;
//   - 'matricule' : réserve les bordereaux de création à la division
//                   qui tient la matricule des agents (la Solde).
// Le second alias est écrit sous forme de chaîne : Laravel l'accepte
// exactement comme « ::class » et le conteneur instancie la classe.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RestreindreRole::class,
            'matricule' => 'App\Http\Middleware\ExigerDivisionMatricule',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
