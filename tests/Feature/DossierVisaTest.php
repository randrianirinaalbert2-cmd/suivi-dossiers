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

class DossierVisaTest extends TestCase
{
    use RefreshDatabase;

    private User $utilisateur;

    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Visa']);
        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $this->division->id,
        ]);
    }

    private function dossier(array $surcharge = []): Dossier
    {
        $service = ServiceOrigine::create(['nom' => 'DSP']);
        $bordereau = BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/001',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
        ]);
        $type = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => 'V03',
            'libelle' => 'Intégration',
        ]);
        $agent = Agent::create([
            'im' => '111222',
            'nom' => 'RAKOTO',
            'prenoms' => 'Solo',
        ]);

        return Dossier::create(array_merge([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $this->division->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::Recu,
            'pole' => PoleDossier::Visa,
        ], $surcharge));
    }

    public function test_le_dossier_au_visa_peut_etre_transmis_au_solde(): void
    {
        $dossier = $this->dossier();
        $dossier->update([
            'num_visa' => 'VIS-2026-001',
            'date_visa' => '2026-10-15',
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.transmettre-solde', $dossier))
            ->assertRedirect(route('dossiers.show', $dossier));

        $dossier->refresh();

        $this->assertSame(PoleDossier::Solde, $dossier->pole);
        $this->assertTrue($dossier->passe_par_visa);
        $this->assertSame('VIS-2026-001', $dossier->num_visa);
        $this->assertSame('2026-10-15', $dossier->date_visa->toDateString());
        $this->assertNull($dossier->reference_acte);

        $this->assertDatabaseHas('historiques', [
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $this->utilisateur->id,
            'type_action' => 'visa',
            'observation' => 'Contrôle du visa effectué : dossier transmis au pôle Solde. Numéro repris : VIS-2026-001.',
        ]);
    }

    public function test_la_transmission_ne_recalcule_pas_la_date_de_fin_de_traitement(): void
    {
        $dossier = $this->dossier();
        $dossier->update(['date_visa' => '2026-10-15']);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.transmettre-solde', $dossier));

        $this->assertSame('2026-10-15', $dossier->refresh()->date_visa->toDateString());
    }

    public function test_un_dossier_deja_au_solde_ne_peut_pas_etre_revise(): void
    {
        $dossier = $this->dossier(['pole' => PoleDossier::Solde]);

        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $dossier))
            ->post(route('dossiers.transmettre-solde', $dossier))
            ->assertRedirect(route('dossiers.show', $dossier))
            ->assertSessionHas('erreur', 'Seul un dossier au pôle Visa peut être transmis au pôle Solde.');

        $this->assertDatabaseMissing('historiques', [
            'dossier_id' => $dossier->id,
            'type_action' => 'visa',
        ]);
    }

    public function test_la_fiche_visa_ne_montre_pas_la_reference_de_lacte(): void
    {
        $dossier = $this->dossier();

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Références visa')
            ->assertDontSee('Réf. acte')
            ->assertDontSee('name="reference_acte"', false);
    }

    public function test_le_formulaire_visa_ne_propose_pas_la_reference_de_lacte(): void
    {
        $dossier = $this->dossier();

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.create', ['bordereau_recu_id' => $dossier->bordereau_recu_id]))
            ->assertOk()
            ->assertSee('Références du visa')
            ->assertDontSee('name="reference_acte"', false);
    }

    public function test_un_dossier_visa_ignore_la_reference_dacte_meme_si_envoyee(): void
    {
        $existant = $this->dossier();
        $agent = Agent::create([
            'im' => '333444',
            'nom' => 'RANDRIA',
            'prenoms' => 'Hery',
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $existant->bordereau_recu_id,
                'agent_id' => $agent->id,
                'type_dossier_id' => $existant->type_dossier_id,
                'pole' => PoleDossier::Visa->value,
                'reference_acte' => 'ARR-2026-77',
            ])
            ->assertRedirect();

        $cree = Dossier::where('agent_id', $agent->id)->firstOrFail();

        $this->assertSame(PoleDossier::Visa, $cree->pole);
        $this->assertNull($cree->reference_acte);
    }
}
