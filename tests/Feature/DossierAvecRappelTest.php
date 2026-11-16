<?php

namespace Tests\Feature;

use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\User;
use Database\Seeders\PiecesSoldeOfficiellesSeeder;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierAvecRappelTest extends TestCase
{
    use RefreshDatabase;

    private User $utilisateur;

    private BordereauRecu $bordereau;

    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferentielSeeder::class);
        $this->seed(PiecesSoldeOfficiellesSeeder::class);

        $this->utilisateur = User::where('courriel', 'admin@suivi.test')->firstOrFail();
        $this->agent = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);

        $division = Division::where('nom', 'Solde')->firstOrFail();

        $this->bordereau = BordereauRecu::create([
            'division_id' => $division->id,
            'service_origine_id' => ServiceOrigine::orderBy('id')->firstOrFail()->id,
            'lieu_id' => Lieu::orderBy('code')->firstOrFail()->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/001',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
        ]);
    }

    private function typeSolde(string $code): TypeDossier
    {
        return TypeDossier::where('code', $code)->whereHas('division', fn ($q) => $q->where('nom', 'Solde'))->firstOrFail();
    }

    private function dossier(TypeDossier $type, bool $avecRappel): Dossier
    {
        return $this->bordereau->dossiers()->create([
            'division_id' => $this->bordereau->division_id,
            'agent_id' => $this->agent->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::Recu,
            'avec_rappel' => $avecRappel,
        ]);
    }

    public function test_le_rappel_porte_l_acte_formate_a_quatre_exemplaires_pour_l_integration(): void
    {
        $dossier = $this->dossier($this->typeSolde('10'), true);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Rappel de solde')
            ->assertSee('acte formaté exigé en 4 exemplaires')
            ->assertSee('4 ex.');
    }

    public function test_sans_rappel_l_acte_formate_reste_a_deux_exemplaires(): void
    {
        $dossier = $this->dossier($this->typeSolde('10'), false);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('2 ex.')
            ->assertDontSee('4 ex.');
    }

    public function test_le_rappel_ne_change_rien_pour_les_autres_types(): void
    {
        $dossier = $this->dossier($this->typeSolde('03'), true);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertDontSee('Rappel de solde')
            ->assertDontSee('4 ex.');
    }

    public function test_le_rappel_est_enregistre_a_la_creation_du_dossier(): void
    {
        $type = $this->typeSolde('17');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'agent_id' => $this->agent->id,
                'type_dossier_id' => $type->id,
                'avec_rappel' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('dossiers', [
            'type_dossier_id' => $type->id,
            'avec_rappel' => 1,
        ]);
    }

    public function test_le_formulaire_propose_le_rappel_seulement_pour_les_types_concernes(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.create'))
            ->assertOk()
            ->assertSee('Avec rappel (acte formaté en 4 exemplaires)');
    }
}
