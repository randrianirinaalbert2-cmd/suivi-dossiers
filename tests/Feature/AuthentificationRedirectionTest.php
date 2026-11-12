<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthentificationRedirectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_racine_redirige_vers_la_page_de_connexion(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }

    public function test_la_page_accueil_laravel_par_defaut_nest_plus_affichee(): void
    {
        $this->get('/')
            ->assertDontSee('Laravel');
    }

    public function test_la_racine_redirige_vers_le_dashboard_quand_une_session_est_ouverte(): void
    {
        $utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
        ]);

        $this->actingAs($utilisateur)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_la_page_de_connexion_est_accessible(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('SRSP');
    }
}
