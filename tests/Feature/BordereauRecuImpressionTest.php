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

class BordereauRecuImpressionTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private BordereauRecu $bordereau;

    private User $utilisateur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $service = ServiceOrigine::create(['nom' => 'DSP']);

        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
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
            'observation' => 'Envoi mensuel.',
        ]);

        $type = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => '02',
            'libelle' => 'Création de droit à pension',
        ]);

        $agent = Agent::create([
            'im' => '111222',
            'nom' => 'RAKOTO',
            'prenoms' => 'Jean',
        ]);

        Dossier::create([
            'bordereau_recu_id' => $this->bordereau->id,
            'division_id' => $this->division->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::Recu,
            'pole' => PoleDossier::Solde,
        ]);
    }

    public function test_la_page_dimpression_s_affiche(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.imprimer', $this->bordereau))
            ->assertOk()
            ->assertSee("BORDEREAU D'ENVOI", false)
            ->assertSee('REF-2026/001')
            ->assertSee('DSP')
            ->assertSee('Envoi mensuel.')
            ->assertSee('Arrivée le 10/10/2026')
            ->assertSee('Sous n° 001')
            ->assertDontSee('RÉCÉPISSÉ DE SAISIE', false)
            ->assertDontSee('MINISTERE DE L\'ECONOMIE', false)
            ->assertDontSee('BORDEREAU D\'ENTRÉE', false);
    }

    public function test_la_page_liste_les_dossiers_du_bordereau(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.imprimer', $this->bordereau))
            ->assertOk()
            ->assertSee('RAKOTO Jean')
            ->assertSee('111222')
            ->assertSee('Dossier portant création de droit à pension')
            ->assertSee('TOTAL');
    }

    public function test_un_bordereau_sans_dossier_affiche_une_mention_vide(): void
    {
        $vide = BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $this->bordereau->service_origine_id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/002',
            'date_bordereau' => '2026-10-11',
            'num_arrivee' => '002',
            'date_arrivee' => '2026-10-11',
        ]);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.imprimer', $vide))
            ->assertOk()
            ->assertSee('TOTAL')
            ->assertSee('00')
            ->assertDontSee('IM :');
    }

    public function test_un_utilisateur_d_une_autre_division_ne_peut_pas_imprimer(): void
    {
        $autreDivision = Division::create(['nom' => 'Visa']);
        $autre = User::factory()->create([
            'role' => RoleUtilisateur::Responsable,
            'division_id' => $autreDivision->id,
        ]);

        $this->actingAs($autre)
            ->get(route('bordereaux.imprimer', $this->bordereau))
            ->assertStatus(403);
    }
}
