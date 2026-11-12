<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierFormulaireAgentTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private BordereauRecu $bordereau;

    private User $utilisateur;

    private TypeDossier $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $service = ServiceOrigine::create(['nom' => 'DREN']);

        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
        ]);

        $this->type = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => '12',
            'libelle' => 'Avenant',
        ]);

        $this->bordereau = BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/001',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
        ]);
    }

    public function test_le_formulaire_propose_la_saisie_d_un_nouvel_agent(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.create', ['bordereau_recu_id' => $this->bordereau->id]))
            ->assertOk()
            ->assertSee('pas encore dans la liste')
            ->assertSee('name="agent_im"', false)
            ->assertSee('name="agent_nom"', false)
            ->assertSee('name="agent_prenoms"', false);
    }

    public function test_on_cree_un_dossier_avec_un_agent_existant(): void
    {
        $agent = Agent::create(['im' => '382515', 'nom' => 'ANDRIAMBELOSON', 'prenoms' => 'Pierre']);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'agent_id' => $agent->id,
                'type_dossier_id' => $this->type->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('agents', 1);
        $this->assertDatabaseHas('dossiers', [
            'bordereau_recu_id' => $this->bordereau->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $this->type->id,
            'statut' => StatutDossier::Recu->value,
        ]);
    }

    public function test_on_cree_un_dossier_avec_un_nouvel_agent_saisi_a_la_main(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'agent_im' => '415965',
                'agent_nom' => 'RAVAOARISOLO',
                'agent_prenoms' => 'Berthe',
                'type_dossier_id' => $this->type->id,
            ])
            ->assertRedirect();

        $agent = Agent::where('im', '415965')->first();

        $this->assertNotNull($agent);
        $this->assertSame('RAVAOARISOLO', $agent->nom);
        $this->assertSame('Berthe', $agent->prenoms);
        $this->assertDatabaseHas('dossiers', [
            'bordereau_recu_id' => $this->bordereau->id,
            'agent_id' => $agent->id,
        ]);
    }

    public function test_un_agent_connu_est_reutilise_meme_avec_des_espaces_dans_l_im(): void
    {
        $agent = Agent::create(['im' => '382515', 'nom' => 'ANDRIAMBELOSON', 'prenoms' => 'Pierre']);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'agent_im' => '382 515',
                'agent_nom' => 'ANDRIAMBELOSON',
                'agent_prenoms' => 'Pierre',
                'type_dossier_id' => $this->type->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('agents', 1);
        $this->assertDatabaseHas('dossiers', ['agent_id' => $agent->id]);
    }

    public function test_sans_agent_ni_im_la_saisie_est_refusee(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'type_dossier_id' => $this->type->id,
            ])
            ->assertSessionHasErrors(['agent_im', 'agent_nom']);

        $this->assertDatabaseCount('dossiers', 0);
    }
}
