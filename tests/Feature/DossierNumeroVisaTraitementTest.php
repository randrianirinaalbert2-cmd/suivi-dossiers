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

class DossierNumeroVisaTraitementTest extends TestCase
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
            'reference' => 'REF-2026/118',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '118',
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
            'statut' => StatutDossier::Verifie,
            'pole' => PoleDossier::Visa,
            'passe_par_visa' => true,
        ], $surcharge));
    }

    public function test_un_dossier_au_visa_ne_peut_pas_etre_declare_traite_sans_numero(): void
    {
        $dossier = $this->dossier();

        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $dossier))
            ->post(route('dossiers.statut', $dossier), ['statut' => 'traite'])
            ->assertRedirect(route('dossiers.show', $dossier))
            ->assertSessionHasErrors('num_visa');

        $this->assertSame(StatutDossier::Verifie, $dossier->refresh()->statut);
        $this->assertSame(0, $dossier->historiques()->count());
    }

    public function test_le_numero_du_visa_et_la_date_de_fin_de_traitement_sont_traces(): void
    {
        $dossier = $this->dossier();

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $dossier), [
                'statut' => 'traite',
                'num_visa' => 'VIS-2026-118',
                'observation' => 'Dossier traité.',
            ])
            ->assertRedirect(route('dossiers.show', $dossier));

        $dossier->refresh();

        $this->assertSame(StatutDossier::Traite, $dossier->statut);
        $this->assertSame('VIS-2026-118', $dossier->num_visa);
        $this->assertSame(now()->toDateString(), $dossier->date_visa->toDateString());

        $historique = $dossier->historiques()->latest('id')->firstOrFail();
        $this->assertStringContainsString('VIS-2026-118', $historique->observation);
        $this->assertStringContainsString('Date de fin de traitement', $historique->observation);

        $details = $historique->detailsTracabilite();
        $this->assertSame('VIS-2026-118', $details['num_visa']);
        $this->assertSame(now()->format('d/m/Y'), $details['date_fin_traitement']);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('N° venu du visa', false)
            ->assertSee('Date de fin de traitement')
            ->assertSee('VIS-2026-118');
    }

    public function test_un_dossier_du_pole_solde_reste_traitable_sans_numero_de_visa(): void
    {
        $dossier = $this->dossier([
            'pole' => PoleDossier::Solde,
            'passe_par_visa' => false,
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $dossier), ['statut' => 'traite'])
            ->assertRedirect(route('dossiers.show', $dossier));

        $this->assertSame(StatutDossier::Traite, $dossier->refresh()->statut);
    }
}
