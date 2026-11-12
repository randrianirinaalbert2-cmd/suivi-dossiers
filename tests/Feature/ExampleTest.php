<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La racine de l'application redirige vers la page de connexion
     * lorsqu'aucune session n'est ouverte (la page d'accueil Laravel
     * par défaut n'est plus exposée).
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
