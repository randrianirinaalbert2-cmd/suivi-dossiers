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

class DossierConfirmationSituationTest extends TestCase
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
            'reference' => 'REF-2026/061',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '061',
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

    public function test_un_dossier_17_ne_peut_pas_etre_mis_en_traitement_avant_confirmation(): void
    {
        $dossier = $this->dossier('17');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $dossier), ['statut' => 'en_traitement'])
            ->assertRedirect()
            ->assertSessionHas('erreur');

        $this->assertSame(StatutDossier::Recu, $dossier->fresh()->statut);
        $this->assertFalse($dossier->fresh()->situationConfirmee());
    }

    public function test_la_confirmation_17_enregistre_l_interruption_et_trace_l_action(): void
    {
        $dossier = $this->dossier('17');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $dossier), ['interruption_contrat_plus_3_mois' => '1'])
            ->assertRedirect(route('dossiers.show', $dossier));

        $dossier->refresh();

        $this->assertTrue($dossier->interruption_contrat_plus_3_mois);
        $this->assertTrue($dossier->situationConfirmee());

        $confirmation = $dossier->derniereConfirmationSituation();

        $this->assertSame('situation_confirmee', $confirmation->type_action);
        $this->assertSame($this->utilisateur->id, $confirmation->utilisateur_id);
        $this->assertStringContainsString('plus de 3 mois', $confirmation->observation);

        $this->assertContains(
            'Attestation de non interruption de service',
            $dossier->piecesExigees()->map(fn ($piece) => $piece->typePiece?->libelle)->all()
        );
    }

    public function test_apres_confirmation_le_dossier_17_passe_en_traitement(): void
    {
        $dossier = $this->dossier('17');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $dossier), ['interruption_contrat_plus_3_mois' => '0']);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $dossier), ['statut' => 'en_traitement'])
            ->assertSessionHas('succes');

        $this->assertSame(StatutDossier::EnTraitement, $dossier->fresh()->statut);
    }

    public function test_la_confirmation_60_exige_la_situation_familiale(): void
    {
        $dossier = $this->dossier('60');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $dossier), ['nombre_enfants' => 2])
            ->assertSessionHasErrors('situation_familiale');

        $this->assertFalse($dossier->fresh()->situationConfirmee());
    }

    public function test_la_confirmation_60_met_a_jour_les_pieces_exigees(): void
    {
        $dossier = $this->dossier('60');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $dossier), [
                'nombre_enfants' => 2,
                'situation_familiale' => 'marie',
                'enfant_moins_de_4_ans' => '1',
                'enfant_deja_integre' => '0',
            ])
            ->assertSessionHasNoErrors();

        $dossier->refresh();

        $this->assertSame('marie', $dossier->situation_familiale);
        $this->assertTrue($dossier->situationConfirmee());

        $libelles = $dossier->piecesExigees()->map(fn ($piece) => $piece->typePiece?->libelle)->all();

        $this->assertContains('Acte de mariage', $libelles);
        $this->assertNotContains('Certificat de célibat', $libelles);
        $this->assertContains('Attestation de non paiement', $libelles);

        $acte = $dossier->piecesExigees()
            ->first(fn ($piece) => $piece->typePiece?->libelle === 'Acte formaté');

        $this->assertSame(4, $acte->nb_exemplaires);
    }

    public function test_la_reception_des_pieces_est_bloquee_avant_confirmation(): void
    {
        $dossier = $this->dossier('60');
        $piece = TypePiece::where('libelle', 'Certificat administratif')->firstOrFail();

        $this->actingAs($this->utilisateur)
            ->postJson(route('dossiers.pieces', $dossier), ['type_piece_id' => $piece->id])
            ->assertStatus(422);

        $this->assertDatabaseMissing('pieces_recues', [
            'dossier_id' => $dossier->id,
            'type_piece_id' => $piece->id,
        ]);
    }

    public function test_la_fiche_affiche_l_attente_puis_la_confirmation(): void
    {
        $dossier = $this->dossier('60');

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('En attente de confirmation')
            ->assertSee('Liste provisoire');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $dossier), [
                'nombre_enfants' => 1,
                'situation_familiale' => 'celibataire',
            ]);

        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Confirmée le')
            ->assertDontSee('Liste provisoire');
    }

    public function test_une_modification_de_la_situation_invalide_la_confirmation(): void
    {
        $dossier = $this->dossier('60');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $dossier), [
                'nombre_enfants' => 1,
                'situation_familiale' => 'celibataire',
            ]);

        $this->assertTrue($dossier->fresh()->situationConfirmee());

        $dossier->fresh()->update(['nombre_enfants' => 2]);

        $this->assertFalse($dossier->fresh()->situationConfirmee());
    }

    public function test_la_liste_signale_les_dossiers_dont_la_situation_reste_a_confirmer(): void
    {
        $aConfirmer = $this->dossier('60');
        $confirme = $this->dossier('17');
        $this->dossier('02');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.situation', $confirme), ['interruption_contrat_plus_3_mois' => '0']);

        $reponse = $this->actingAs($this->utilisateur)
            ->get(route('dossiers.index'))
            ->assertOk();

        // Un seul dossier doit porter le repère : celui du 60 non confirmé.
        $this->assertSame(1, substr_count($reponse->getContent(), 'Situation à confirmer'));
    }

    public function test_les_types_sans_condition_ne_sont_pas_bloques(): void
    {
        $dossier = $this->dossier('02');

        $this->assertFalse($dossier->situationAConfirmer());

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $dossier), ['statut' => 'en_traitement'])
            ->assertSessionHas('succes');

        $this->assertSame(StatutDossier::EnTraitement, $dossier->fresh()->statut);
    }
}
