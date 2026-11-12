<?php

namespace Tests\Feature;

use App\Enums\PoleDossier;
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

class DossierSaisieMultipleTest extends TestCase
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
            'nombre_dossiers_attendus' => 16,
        ]);
    }

    public function test_la_page_de_saisie_en_masse_s_affiche(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.create-plusieurs', $this->bordereau))
            ->assertOk()
            ->assertSee('Saisie en masse')
            ->assertSee('REF-2026/001')
            ->assertSee('16')
            ->assertSee('saisie-multiple/'.$this->bordereau->id, false);
    }

    public function test_on_peut_creer_plusieurs_dossiers_d_un_coup(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '382515', 'agent_nom' => 'ANDRIAMBELOSON', 'agent_prenoms' => 'Pierre', 'type_dossier_id' => $this->type->id],
                    ['agent_im' => '415965', 'agent_nom' => 'RAVAOARISOLO', 'agent_prenoms' => 'Berthe', 'type_dossier_id' => $this->type->id],
                ],
            ])
            ->assertRedirect(route('bordereaux.show', $this->bordereau));

        $this->assertDatabaseCount('dossiers', 2);
        $this->assertDatabaseCount('agents', 2);
        $this->assertDatabaseHas('dossiers', [
            'bordereau_recu_id' => $this->bordereau->id,
            'division_id' => $this->division->id,
            'pole' => PoleDossier::Solde->value,
            'statut' => StatutDossier::Recu->value,
        ]);
        $this->assertDatabaseHas('historiques', ['type_action' => 'creation']);
    }

    public function test_l_im_existant_avec_espaces_est_retrouve_et_non_duplique(): void
    {
        Agent::create(['im' => '382515', 'nom' => 'ANDRIAMBELOSON', 'prenoms' => 'Pierre']);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '382 515', 'agent_nom' => 'ANDRIAMBELOSON', 'agent_prenoms' => 'Pierre', 'type_dossier_id' => $this->type->id],
                ],
            ]);

        $this->assertDatabaseCount('agents', 1);
        $this->assertDatabaseCount('dossiers', 1);
    }

    public function test_une_ligne_en_double_est_rejetee(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '382515', 'agent_nom' => 'ANDRIAMBELOSON', 'type_dossier_id' => $this->type->id],
                    ['agent_im' => '382 515', 'agent_nom' => 'ANDRIAMBELOSON', 'type_dossier_id' => $this->type->id],
                ],
            ])
            ->assertSessionHasErrors('dossiers');

        $this->assertDatabaseCount('dossiers', 0);
    }

    public function test_les_lignes_vides_sont_ignorees(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '297407', 'agent_nom' => 'RASOLOMAMPIONONA', 'agent_prenoms' => 'Xavier', 'type_dossier_id' => $this->type->id],
                    ['agent_im' => '', 'agent_nom' => '', 'agent_prenoms' => '', 'type_dossier_id' => ''],
                ],
            ]);

        $this->assertDatabaseCount('dossiers', 1);
    }

    public function test_une_saisie_entierement_vide_est_rejetee(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '', 'agent_nom' => '', 'agent_prenoms' => '', 'type_dossier_id' => ''],
                ],
            ])
            ->assertSessionHasErrors('dossiers');

        $this->assertDatabaseCount('dossiers', 0);
    }

    public function test_un_type_d_autre_division_est_rejete(): void
    {
        $autreDivision = Division::create(['nom' => 'Visa']);
        $autreType = TypeDossier::create([
            'division_id' => $autreDivision->id,
            'code' => 'V02',
            'libelle' => 'Avenant (agent EFA)',
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '111111', 'agent_nom' => 'TEST', 'agent_prenoms' => 'Agent', 'type_dossier_id' => $autreType->id],
                ],
            ])
            ->assertSessionHasErrors('dossiers');

        $this->assertDatabaseCount('dossiers', 0);
    }

    public function test_un_alerte_si_moins_de_dossiers_que_le_bordereau_papier(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $this->bordereau), [
                'dossiers' => [
                    ['agent_im' => '297407', 'agent_nom' => 'RASOLOMAMPIONONA', 'type_dossier_id' => $this->type->id],
                ],
            ])
            ->assertSessionHas('erreur');

        $this->assertDatabaseCount('dossiers', 1);
    }

    public function test_le_pole_de_la_division_est_propose_par_defaut(): void
    {
        $divisionVisa = Division::create(['nom' => 'Visa']);
        $typeVisa = TypeDossier::create([
            'division_id' => $divisionVisa->id,
            'code' => 'V02',
            'libelle' => 'Avenant (agent EFA)',
        ]);
        $bordereauVisa = BordereauRecu::create([
            'division_id' => $divisionVisa->id,
            'service_origine_id' => ServiceOrigine::create(['nom' => 'DRSP'])->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/002',
            'objet' => 'POUR COMPETENCE',
            'date_bordereau' => '2026-10-12',
            'num_arrivee' => '002',
            'date_arrivee' => '2026-10-12',
            'nombre_dossiers_attendus' => 3,
        ]);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.create-plusieurs', $bordereauVisa))
            ->assertOk()
            ->assertSee(PoleDossier::Visa->label());

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.en-masse', $bordereauVisa), [
                'dossiers' => [
                    ['agent_im' => '999999', 'agent_nom' => 'AGENT', 'type_dossier_id' => $typeVisa->id],
                ],
            ]);

        $this->assertDatabaseHas('dossiers', [
            'division_id' => $divisionVisa->id,
            'pole' => PoleDossier::Visa->value,
        ]);
    }
}
