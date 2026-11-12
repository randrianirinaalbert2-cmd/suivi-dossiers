<?php

namespace Tests\Feature;

use App\Enums\PoleDossier;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierStatutPermissionTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private BordereauRecu $bordereau;

    private TypeDossier $type;

    private Agent $agent;

    private User $chef;

    private User $responsable;

    private User $autreResponsable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $service = ServiceOrigine::create(['nom' => 'DSP']);

        $this->bordereau = BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => User::factory()->create()->id,
            'reference' => 'REF-2026/001',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
        ]);

        $this->type = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => '02',
            'libelle' => 'Création de droit à pension',
        ]);

        $this->agent = Agent::create([
            'im' => '111222',
            'nom' => 'RAKOTO',
            'prenoms' => 'Jean',
        ]);

        $this->chef = User::factory()->create([
            'role' => RoleUtilisateur::ChefDivision,
            'division_id' => $this->division->id,
        ]);

        $this->responsable = User::factory()->create([
            'role' => RoleUtilisateur::Responsable,
            'division_id' => $this->division->id,
        ]);

        $this->autreResponsable = User::factory()->create([
            'role' => RoleUtilisateur::Responsable,
            'division_id' => $this->division->id,
        ]);
    }

    private function dossier(StatutDossier $statut, User $responsable): Dossier
    {
        return Dossier::create([
            'bordereau_recu_id' => $this->bordereau->id,
            'division_id' => $this->division->id,
            'agent_id' => $this->agent->id,
            'type_dossier_id' => $this->type->id,
            'responsable_id' => $responsable->id,
            'statut' => $statut,
            'pole' => PoleDossier::Solde,
        ]);
    }

    public function test_un_responsable_non_chef_ne_peut_pas_transmettre(): void
    {
        $dossier = $this->dossier(StatutDossier::Verifie, $this->responsable);

        $this->actingAs($this->responsable)
            ->from(route('dossiers.show', $dossier))
            ->post(route('dossiers.statut', $dossier), ['statut' => StatutDossier::Transmis->value])
            ->assertRedirect(route('dossiers.show', $dossier))
            ->assertSessionHas('erreur', 'Seul le chef de division peut transmettre un dossier.');

        $this->assertSame(StatutDossier::Verifie, $dossier->refresh()->statut);
    }

    public function test_le_chef_de_division_peut_transmettre(): void
    {
        $dossier = $this->dossier(StatutDossier::Verifie, $this->responsable);

        $this->actingAs($this->chef)
            ->post(route('dossiers.statut', $dossier), ['statut' => StatutDossier::Transmis->value])
            ->assertRedirect(route('dossiers.show', $dossier));

        $this->assertSame(StatutDossier::Transmis, $dossier->refresh()->statut);
    }

    public function test_un_responsable_autre_ne_peut_pas_archiver(): void
    {
        $dossier = $this->dossier(StatutDossier::Transmis, $this->responsable);

        $this->actingAs($this->autreResponsable)
            ->from(route('dossiers.show', $dossier))
            ->post(route('dossiers.statut', $dossier), ['statut' => StatutDossier::Archive->value])
            ->assertRedirect(route('dossiers.show', $dossier))
            ->assertSessionHas('erreur', 'Seul le responsable du dossier peut l\'archiver.');

        $this->assertSame(StatutDossier::Transmis, $dossier->refresh()->statut);
    }

    public function test_le_responsable_du_dossier_peut_l_archiver(): void
    {
        $dossier = $this->dossier(StatutDossier::Transmis, $this->responsable);

        $this->actingAs($this->responsable)
            ->post(route('dossiers.statut', $dossier), ['statut' => StatutDossier::Archive->value])
            ->assertRedirect(route('dossiers.show', $dossier));

        $this->assertSame(StatutDossier::Archive, $dossier->refresh()->statut);
    }

    public function test_le_responsable_non_chef_ne_voit_que_les_statuts_ouverts(): void
    {
        $dossier = $this->dossier(StatutDossier::Verifie, $this->responsable);

        $this->actingAs($this->responsable)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Nouveau statut')
            ->assertDontSee('value="transmis"', false)
            ->assertSee('Traité');
    }

    public function test_la_fiche_signale_une_suite_bloquee_et_non_un_dossier_cloture(): void
    {
        $dossier = $this->dossier(StatutDossier::Transmis, $this->responsable);

        $this->actingAs($this->autreResponsable)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee("Le dossier n'est pas clôturé", false)
            ->assertDontSee('Nouveau statut')
            ->assertDontSee('Dossier clôturé');
    }

    public function test_un_dossier_archive_n_affiche_aucun_formulaire(): void
    {
        $dossier = $this->dossier(StatutDossier::Archive, $this->responsable);

        $this->actingAs($this->responsable)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Dossier clôturé')
            ->assertDontSee('Nouveau statut');
    }

    public function test_l_administrateur_peut_tout_faire(): void
    {
        $admin = User::factory()->create(['role' => RoleUtilisateur::Administrateur]);
        $dossier = $this->dossier(StatutDossier::Verifie, $this->responsable);

        $this->assertTrue($admin->peutChangerStatut($dossier, StatutDossier::Transmis));
        $this->assertTrue($admin->peutChangerStatut($dossier, StatutDossier::Archive));
    }
}
