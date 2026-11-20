<?php

namespace Tests\Feature;

use App\Enums\PoleDossier;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\Historique;
use App\Models\PieceRequise;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\TypePiece;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierMotifRejetTest extends TestCase
{
    use RefreshDatabase;

    private User $utilisateur;

    private Dossier $dossier;

    private TypePiece $pieceDemande;

    private TypePiece $pieceAttestation;

    protected function setUp(): void
    {
        parent::setUp();

        $division = Division::create(['nom' => 'Solde']);
        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $division->id,
        ]);

        $service = ServiceOrigine::create(['nom' => 'DSP']);
        $bordereau = BordereauRecu::create([
            'division_id' => $division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/MOTIF',
            'date_bordereau' => '2026-11-20',
            'num_arrivee' => '401',
            'date_arrivee' => '2026-11-20',
        ]);
        $type = TypeDossier::create([
            'division_id' => $division->id,
            'code' => 'S01',
            'libelle' => 'Mutation',
        ]);

        $this->pieceDemande = TypePiece::create(['libelle' => 'Demande du agent']);
        $this->pieceAttestation = TypePiece::create(['libelle' => 'Attestation de non paiement']);

        foreach ([$this->pieceDemande, $this->pieceAttestation] as $piece) {
            PieceRequise::create([
                'type_dossier_id' => $type->id,
                'type_piece_id' => $piece->id,
                'nb_exemplaires' => 1,
            ]);
        }

        $agent = Agent::create([
            'im' => '776655',
            'nom' => 'RAKOTO',
            'prenoms' => 'Solo',
        ]);

        $this->dossier = Dossier::create([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $division->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::Recu,
            'pole' => PoleDossier::Solde,
        ]);
    }

    public function test_un_rejet_sans_motif_est_refuse(): void
    {
        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $this->dossier))
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'rejete'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasErrors('motif_rejet');

        $this->assertSame(StatutDossier::Recu, $this->dossier->refresh()->statut);
        $this->assertSame(0, Historique::count());
    }

    public function test_un_rejet_pour_pieces_manquantes_liste_les_pieces_absentes(): void
    {
        $this->dossier->piecesRecues()->create(['type_piece_id' => $this->pieceDemande->id]);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), [
                'statut' => 'rejete',
                'motif_rejet' => 'pieces_manquantes',
            ])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $this->assertSame(StatutDossier::Rejete, $this->dossier->refresh()->statut);
        $this->assertStringContainsString('Motif de rejet : Pièces manquantes.', $this->dossier->observation);
        $this->assertStringContainsString('Pièces manquantes : Attestation de non paiement.', $this->dossier->observation);
        $this->assertStringNotContainsString('Demande du agent', $this->dossier->observation);

        $historique = Historique::firstOrFail();
        $this->assertSame('rejete', $historique->nouveau_statut);
        $this->assertSame($this->dossier->observation, $historique->observation);
    }

    public function test_le_motif_peut_preciser_un_numero_de_compte_incomplet(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), [
                'statut' => 'rejete',
                'motif_rejet' => 'piece_incomplete',
                'detail_motif' => 'Numéro de compte incomplet',
            ])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $this->dossier->refresh();

        $this->assertSame(StatutDossier::Rejete, $this->dossier->statut);
        $this->assertStringContainsString('Motif de rejet : Pièce incomplète ou non conforme.', $this->dossier->observation);
        $this->assertStringContainsString('Précision : Numéro de compte incomplet.', $this->dossier->observation);
    }

    public function test_le_motif_autre_exige_une_precision(): void
    {
        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $this->dossier))
            ->post(route('dossiers.statut', $this->dossier), [
                'statut' => 'rejete',
                'motif_rejet' => 'autre',
            ])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasErrors('detail_motif');

        $this->assertSame(StatutDossier::Recu, $this->dossier->refresh()->statut);
    }

    public function test_lobservation_libre_est_conserve_apres_le_motif(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), [
                'statut' => 'rejete',
                'motif_rejet' => 'piece_incomplete',
                'detail_motif' => 'Acte illisible',
                'observation' => 'Prévenir l’agent.',
            ])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $observation = $this->dossier->refresh()->observation;

        $this->assertStringContainsString('Précision : Acte illisible.', $observation);
        $this->assertStringContainsString('Prévenir l’agent.', $observation);
    }

    public function test_un_dossier_incomplet_exige_aussi_un_motif(): void
    {
        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $this->dossier))
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'incomplet'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasErrors('motif_rejet');

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), [
                'statut' => 'incomplet',
                'motif_rejet' => 'pieces_manquantes',
            ])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $this->assertSame(StatutDossier::Incomplet, $this->dossier->refresh()->statut);
        $this->assertStringContainsString('Motif de rejet : Pièces manquantes.', $this->dossier->observation);
    }

    public function test_la_fiche_propose_les_motifs_et_la_liste_des_pieces_manquantes(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('dossiers.show', $this->dossier))
            ->assertOk()
            ->assertSee('Motif de rejet (obligatoire)')
            ->assertSee('Pièces manquantes')
            ->assertSee('Pièce incomplète ou non conforme')
            ->assertSee('name="motif_rejet"', false)
            ->assertSee('name="detail_motif"', false)
            ->assertSee('Pièces actuellement manquantes')
            ->assertSee('Attestation de non paiement');
    }

    public function test_un_changement_sans_rejet_ne_demande_pas_de_motif(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'en_traitement'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $this->assertSame(StatutDossier::EnTraitement, $this->dossier->refresh()->statut);
    }
}
