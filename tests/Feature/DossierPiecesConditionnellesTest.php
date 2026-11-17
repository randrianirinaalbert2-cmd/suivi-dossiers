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
use App\Models\TypePiece;
use App\Models\User;
use Database\Seeders\PiecesSoldeOfficiellesSeeder;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierPiecesConditionnellesTest extends TestCase
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
            'reference' => 'REF-2026/060',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '060',
            'date_arrivee' => '2026-10-10',
        ]);
    }

    private function typeSolde(string $code): TypeDossier
    {
        return TypeDossier::where('code', $code)
            ->whereHas('division', fn ($q) => $q->where('nom', 'Solde'))
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $attributs
     */
    private function dossier(string $code, array $attributs = []): Dossier
    {
        return $this->bordereau->dossiers()->create(array_merge([
            'division_id' => $this->bordereau->division_id,
            'agent_id' => $this->agent->id,
            'type_dossier_id' => $this->typeSolde($code)->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::Recu,
        ], $attributs));
    }

    /**
     * @return list<string>
     */
    private function libelles(Dossier $dossier): array
    {
        return $dossier->piecesExigees()
            ->map(fn ($piece) => $piece->typePiece?->libelle)
            ->values()
            ->all();
    }

    private function exemplaires(Dossier $dossier, string $libelle): ?int
    {
        return $dossier->piecesExigees()
            ->first(fn ($piece) => $piece->typePiece?->libelle === $libelle)
            ?->nb_exemplaires;
    }

    public function test_cmvt_17_n_exige_pas_l_attestation_sans_interruption_de_plus_de_3_mois(): void
    {
        $dossier = $this->dossier('17');

        $this->assertNotContains('Attestation de non interruption de service', $this->libelles($dossier));
        $this->assertSame(4, $dossier->completude()['exigees']);
    }

    public function test_cmvt_17_exige_l_attestation_en_cas_d_interruption_de_plus_de_3_mois(): void
    {
        $dossier = $this->dossier('17', ['interruption_contrat_plus_3_mois' => true]);

        $this->assertContains('Attestation de non interruption de service', $this->libelles($dossier));
        $this->assertSame(5, $dossier->completude()['exigees']);
    }

    public function test_cmvt_60_porte_l_acte_formate_a_4_exemplaires_avec_un_enfant_de_moins_de_4_ans(): void
    {
        $sans = $this->dossier('60', ['nombre_enfants' => 1, 'enfant_moins_de_4_ans' => false]);
        $avec = $this->dossier('60', ['nombre_enfants' => 1, 'enfant_moins_de_4_ans' => true]);

        $this->assertSame(2, $this->exemplaires($sans, 'Acte formaté'));
        $this->assertSame(4, $this->exemplaires($avec, 'Acte formaté'));
    }

    public function test_cmvt_60_applique_les_pieces_du_mariage_et_des_enfants(): void
    {
        $dossier = $this->dossier('60', [
            'nombre_enfants' => 3,
            'situation_familiale' => 'marie',
            'enfant_deja_integre' => false,
        ]);

        $libelles = $this->libelles($dossier);

        $this->assertContains('Copie de tous les enfants', $libelles);
        $this->assertContains('Acte de mariage', $libelles);
        $this->assertContains('Attestation de non paiement', $libelles);
        $this->assertNotContains('Certificat de célibat', $libelles);
        $this->assertSame(3, $this->exemplaires($dossier, 'Acte de mariage'));
        $this->assertSame(3, $this->exemplaires($dossier, 'Copie de tous les enfants'));
        $this->assertSame(3, $this->exemplaires($dossier, 'Attestation de non paiement'));
        $this->assertSame(3, $this->exemplaires($dossier, 'Charge des familles (CF)'));
        $this->assertSame(1, $this->exemplaires($dossier, 'Certificat administratif'));
    }

    public function test_cmvt_60_applique_le_celibat_et_dispense_de_l_attestation_si_enfant_deja_integre(): void
    {
        $dossier = $this->dossier('60', [
            'nombre_enfants' => 1,
            'situation_familiale' => 'celibataire',
            'enfant_deja_integre' => true,
        ]);

        $libelles = $this->libelles($dossier);

        $this->assertContains('Certificat de célibat', $libelles);
        $this->assertNotContains('Acte de mariage', $libelles);
        $this->assertNotContains('Attestation de non paiement', $libelles);
        $this->assertSame(3, $this->exemplaires($dossier, 'Certificat de célibat'));
    }

    public function test_cmvt_60_sans_enfant_n_exige_pas_la_copie_des_enfants(): void
    {
        $dossier = $this->dossier('60');

        $this->assertNotContains('Copie de tous les enfants', $this->libelles($dossier));
        $this->assertSame(5, $dossier->completude()['exigees']);
    }

    public function test_la_completude_ignore_les_pieces_conditionnelles_non_applicables(): void
    {
        $dossier = $this->dossier('60', [
            'nombre_enfants' => 0,
            'situation_familiale' => 'celibataire',
        ]);

        $exigees = $dossier->piecesExigees();
        $this->assertSame($exigees->count(), $dossier->completude()['exigees']);

        foreach ($exigees as $piece) {
            $dossier->piecesRecues()->create(['type_piece_id' => $piece->type_piece_id]);
        }

        $this->assertTrue($dossier->fresh()->estComplet());
    }

    public function test_une_piece_conditionnelle_non_applicable_ne_peut_pas_etre_cochee(): void
    {
        $dossier = $this->dossier('17');
        $piece = TypePiece::where('libelle', 'Attestation de non interruption de service')->firstOrFail();

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.pieces', $dossier), ['type_piece_id' => $piece->id])
            ->assertStatus(422);
    }

    public function test_les_conditions_de_la_fiche_sont_affichees_et_enregistrees(): void
    {
        $dossier = $this->dossier('60', [
            'nombre_enfants' => 2,
            'enfant_moins_de_4_ans' => true,
            'situation_familiale' => 'marie',
            'enfant_deja_integre' => true,
        ]);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('dont un de moins de 4 ans')
            ->assertSee('Marié(e)')
            ->assertSee('4 ex.');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'agent_id' => $this->agent->id,
                'type_dossier_id' => $this->typeSolde('60')->id,
                'nombre_enfants' => 2,
                'enfant_moins_de_4_ans' => '1',
                'situation_familiale' => 'marie',
                'enfant_deja_integre' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('dossiers', [
            'type_dossier_id' => $this->typeSolde('60')->id,
            'nombre_enfants' => 2,
            'enfant_moins_de_4_ans' => 1,
            'situation_familiale' => 'marie',
            'enfant_deja_integre' => 1,
        ]);
    }

    public function test_le_type_17_enregistre_l_interruption_de_contrat(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.store'), [
                'bordereau_recu_id' => $this->bordereau->id,
                'agent_id' => $this->agent->id,
                'type_dossier_id' => $this->typeSolde('17')->id,
                'interruption_contrat_plus_3_mois' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('dossiers', [
            'type_dossier_id' => $this->typeSolde('17')->id,
            'interruption_contrat_plus_3_mois' => 1,
        ]);
    }
}
