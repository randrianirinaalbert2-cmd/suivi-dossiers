<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NomApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_de_connexion_affiche_le_nom_unique_de_lapplication(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(config('app.name'))
            ->assertDontSee('Service Régional de la Solde et des Pensions');
    }

    public function test_la_navigation_affiche_le_meme_nom_que_la_page_de_connexion(): void
    {
        $division = Division::create(['nom' => Division::NOM_SOLDE]);
        $utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $division->id,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(config('app.name'));
    }
}
