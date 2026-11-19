<?php

namespace Tests\Feature;

use App\Enums\PoleDossier;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\PieceRequise;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\TypePiece;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DossierVerificationPiecesTest extends TestCase
{
    use RefreshDatabase;

    private User $utilisateur;

    private Dossier $dossier;

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
            'reference' => 'REF-2026/PIECES',
            'date_bordereau' => '2026-11-19',
            'num_arrivee' => '400',
            'date_arrivee' => '2026-11-19',
        ]);
        $type = TypeDossier::create([
            'division_id' => $division->id,
            'code' => 'S01',
            'libelle' => 'Mutation',
        ]);
        $piece = TypePiece::create(['libelle' => 'Demande']);
        PieceRequise::create([
            'type_dossier_id' => $type->id,
            'type_piece_id' => $piece->id,
            'nb_exemplaires' => 1,
        ]);
        $agent = Agent::create([
            'im' => '998877',
            'nom' => 'RAKOTO',
            'prenoms' => 'Solo',
        ]);

        $this->dossier = Dossier::create([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $division->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::EnTraitement,
            'pole' => PoleDossier::Solde,
        ]);
    }

    public function test_un_dossier_sans_pieces_ne_peut_pas_etre_declare_verifie(): void
    {
        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $this->dossier))
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'verifie'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHas('erreur');

        $this->assertSame(StatutDossier::EnTraitement, $this->dossier->refresh()->statut);
        $this->assertSame(0, $this->dossier->historiques()->count());
    }

    public function test_un_dossier_complet_peut_etre_declare_verifie_puis_traite(): void
    {
        $this->dossier->piecesRecues()->create([
            'type_piece_id' => $this->dossier->piecesExigees()->first()->type_piece_id,
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'verifie'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $this->assertSame(StatutDossier::Verifie, $this->dossier->refresh()->statut);

        $this->actingAs($this->utilisateur)
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'traite'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHasNoErrors();

        $this->assertSame(StatutDossier::Traite, $this->dossier->refresh()->statut);
    }

    public function test_un_dossier_verifie_incomplet_ne_peut_pas_etre_declare_traite(): void
    {
        $this->dossier->update(['statut' => StatutDossier::Verifie]);

        $this->actingAs($this->utilisateur)
            ->from(route('dossiers.show', $this->dossier))
            ->post(route('dossiers.statut', $this->dossier), ['statut' => 'traite'])
            ->assertRedirect(route('dossiers.show', $this->dossier))
            ->assertSessionHas('erreur');

        $this->assertSame(StatutDossier::Verifie, $this->dossier->refresh()->statut);
        $this->assertSame(0, $this->dossier->historiques()->count());
    }
}
