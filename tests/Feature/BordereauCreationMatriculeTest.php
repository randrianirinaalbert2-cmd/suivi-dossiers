<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\BordereauCreation;
use App\Models\Destination;
use App\Models\Division;
use App\Models\Lieu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le bordereau de création porte l'IM (matricule) des agents : il relève de
 * la seule division qui tient la matricule (Solde). La division Visa n'en
 * produit pas.
 */
class BordereauCreationMatriculeTest extends TestCase
{
    use RefreshDatabase;

    private Division $solde;

    private Division $visa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->solde = Division::create(['nom' => 'Solde']);
        $this->visa = Division::create(['nom' => 'Visa']);
    }

    private function chefVisa(): User
    {
        return User::factory()->create([
            'role' => RoleUtilisateur::ChefDivision,
            'division_id' => $this->visa->id,
        ]);
    }

    private function chefSolde(): User
    {
        return User::factory()->create([
            'role' => RoleUtilisateur::ChefDivision,
            'division_id' => $this->solde->id,
        ]);
    }

    private function administrateur(): User
    {
        return User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => null,
        ]);
    }

    public function test_le_chef_visa_na_accede_pas_aux_bordereaux_de_creation(): void
    {
        $this->actingAs($this->chefVisa())
            ->get(route('bordereaux-creation.index'))
            ->assertForbidden();
    }

    public function test_le_chef_visa_na_accede_pas_au_formulaire_de_creation(): void
    {
        $this->actingAs($this->chefVisa())
            ->get(route('bordereaux-creation.create'))
            ->assertForbidden();
    }

    public function test_le_chef_visa_na_accede_pas_a_un_bordereau_de_creation(): void
    {
        $lieu = Lieu::create(['code' => '101', 'nom' => 'Ambositra', 'sigle' => 'AMM']);
        $destination = Destination::create(['nom' => 'Direction Generale']);

        $bordereau = BordereauCreation::create([
            'division_id' => $this->solde->id,
            'lieu_id' => $lieu->id,
            'destination_id' => $destination->id,
            'utilisateur_id' => $this->chefSolde()->id,
            'chapitre' => '4200',
            'paquet' => '01',
            'annee' => 2026,
            'num_ordre' => 1,
            'statut' => 'valide',
        ]);

        $this->actingAs($this->chefVisa())
            ->get(route('bordereaux-creation.show', $bordereau))
            ->assertForbidden();
    }

    public function test_le_chef_solde_accede_aux_bordereaux_de_creation(): void
    {
        $this->actingAs($this->chefSolde())
            ->get(route('bordereaux-creation.index'))
            ->assertOk();

        $this->actingAs($this->chefSolde())
            ->get(route('bordereaux-creation.create'))
            ->assertOk();
    }

    public function test_l_administrateur_accede_aux_bordereaux_de_creation(): void
    {
        $this->actingAs($this->administrateur())
            ->get(route('bordereaux-creation.index'))
            ->assertOk();

        $this->actingAs($this->administrateur())
            ->get(route('bordereaux-creation.create'))
            ->assertOk();
    }

    public function test_le_menu_ne_propose_pas_les_bordereaux_de_creation_a_la_division_visa(): void
    {
        $this->actingAs($this->chefVisa())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('bordereaux-creation.index'), false);
    }

    public function test_le_menu_propose_les_bordereaux_de_creation_a_la_division_solde(): void
    {
        $this->actingAs($this->chefSolde())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('bordereaux-creation.index'), false);
    }

    public function test_le_tableau_de_bord_du_chef_visa_ne_comporte_pas_de_carte_bordereaux_de_creation(): void
    {
        $this->actingAs($this->chefVisa())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Bordereaux de création', false);
    }

    public function test_le_formulaire_propose_seulement_la_division_de_la_solde(): void
    {
        $this->actingAs($this->administrateur())
            ->get(route('bordereaux-creation.create'))
            ->assertOk()
            ->assertSee('Solde', false)
            ->assertDontSee('>Visa<', false);
    }
}
