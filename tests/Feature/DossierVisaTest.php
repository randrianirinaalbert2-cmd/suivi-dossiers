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

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.transmettre-solde', $dossier), [
                'num_visa' => 'VIS-2026-001',
                'date_visa' => '2026-10-15',
                'reference_acte' => 'ARR-2026-77',
            ])
            ->assertRedirect(route('dossiers.show', $dossier));

        $dossier->refresh();

        $this->assertSame(PoleDossier::Solde, $dossier->pole);
        $this->assertTrue($dossier->passe_par_visa);
        $this->assertSame('VIS-2026-001', $dossier->num_visa);
        $this->assertSame('2026-10-15', $dossier->date_visa->toDateString());
        $this->assertSame('ARR-2026-77', $dossier->reference_acte);

        $this->assertDatabaseHas('historiques', [
            'dossier_id' => $dossier->id,
            'utilisateur_id' => $this->utilisateur->id,
            'type_action' => 'visa',
        ]);
    }

    public function test_la_date_de_visa_par_defaut_est_aujourd_hui(): void
    {
        $dossier = $this->dossier();

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.transmettre-solde', $dossier));

        $this->assertSame(now()->toDateString(), $dossier->refresh()->date_visa->toDateString());
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
}
