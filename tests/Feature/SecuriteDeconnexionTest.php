<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecuriteDeconnexionTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_page_connectee_nest_pas_conservee_dans_le_cache_du_navigateur(): void
    {
        $division = Division::create(['nom' => Division::NOM_SOLDE]);
        $utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $division->id,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertHeaderContains('Cache-Control', 'no-cache')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');
    }

    public function test_la_page_de_connexion_nest_pas_non_plus_mise_en_cache(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertHeaderContains('Cache-Control', 'no-cache');
    }

    public function test_apres_deconnexion_un_retour_vers_le_tableau_de_bord_renvoie_vers_la_connexion(): void
    {
        $division = Division::create(['nom' => Division::NOM_SOLDE]);
        $utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $division->id,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('dashboard'))
            ->assertOk();

        $this->post(route('logout'))
            ->assertRedirect('/');

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk();
    }

    public function test_la_reponse_de_deconnexion_interdit_aussi_le_cache(): void
    {
        $utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
        ]);

        $this->actingAs($utilisateur)
            ->post(route('logout'))
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertHeaderContains('Cache-Control', 'no-cache');
    }
}
