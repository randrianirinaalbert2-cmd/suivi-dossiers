<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\Historique;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\User;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoriqueDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Division, 2: Dossier}
     */
    private function creerContexte(): array
    {
        $this->seed(ReferentielSeeder::class);

        $utilisateur = User::where('courriel', 'admin@suivi.test')->firstOrFail();
        $division = Division::where('nom', 'Solde')->firstOrFail();
        $agent = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);

        $bordereau = BordereauRecu::create([
            'division_id' => $division->id,
            'service_origine_id' => ServiceOrigine::orderBy('id')->firstOrFail()->id,
            'lieu_id' => Lieu::orderBy('code')->firstOrFail()->id,
            'utilisateur_id' => $utilisateur->id,
            'reference' => 'REF-2026/062',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '062',
            'date_arrivee' => '2026-10-10',
        ]);

        $typeDossier = TypeDossier::where('code', '17')
            ->whereHas('division', fn ($q) => $q->where('nom', 'Solde'))
            ->firstOrFail();

        $dossier = $bordereau->dossiers()->create([
            'division_id' => $division->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $typeDossier->id,
            'responsable_id' => $utilisateur->id,
            'statut' => StatutDossier::EnTraitement,
        ]);

        return [$utilisateur, $division, $dossier];
    }

    /**
     * La date est la colonne created_at du modele et n'est pas assignable en
     * masse : on desactive les horodatages puis on l'affecte directement.
     */
    private function ajouterHistorique(Dossier $dossier, User $utilisateur, array $donnees, string $date): Historique
    {
        return Historique::withoutTimestamps(function () use ($dossier, $utilisateur, $donnees, $date) {
            $action = Historique::create(array_merge([
                'dossier_id' => $dossier->id,
                'utilisateur_id' => $utilisateur->id,
            ], $donnees));
            $action->date_heure_action = $date;
            $action->save();

            return $action;
        });
    }

    public function test_les_pieces_recues_sont_regroupees_sur_une_seule_ligne(): void
    {
        [$utilisateur, $division, $dossier] = $this->creerContexte();

        $historique = $this->ajouterHistorique($dossier, $utilisateur, [
            'type_action' => 'changement_statut',
            'ancien_statut' => StatutDossier::Recu->value,
            'nouveau_statut' => StatutDossier::EnTraitement->value,
            'observation' => 'Observation de tracabilite unique 12345',
        ], '2026-11-19 14:07:09');

        $autreResponsable = User::factory()->create([
            'nom' => 'RANDRIAMANANTENA',
            'role' => RoleUtilisateur::Responsable,
            'division_id' => $division->id,
        ]);

        $piece1 = $this->ajouterHistorique($dossier, $utilisateur, [
            'type_action' => 'ajout_piece',
            'observation' => 'Pièce : Acte de naissance',
        ], '2026-11-18 09:00:00');

        $piece2 = $this->ajouterHistorique($dossier, $autreResponsable, [
            'type_action' => 'ajout_piece',
            'observation' => 'Pièce : Attestation de non paiement',
        ], '2026-11-19 10:30:00');

        $details = $historique->detailsTracabilite();

        $this->assertSame('Observation de tracabilite unique 12345', $details['observation']);
        $this->assertSame('19/11/2026 à 14:07:09', $details['date_heure']);
        $this->assertSame('admin@suivi.test', $details['courriel']);
        $this->assertSame('Reçu', $details['statut_avant']);
        $this->assertSame('En traitement', $details['statut_apres']);
        $this->assertCount(2, $details['pieces_prises']);
        $this->assertSame('Acte de naissance', $details['pieces_prises'][0]['piece']);
        $this->assertSame('18/11/2026 à 09:00', $details['pieces_prises'][0]['date']);
        $this->assertSame('Attestation de non paiement', $details['pieces_prises'][1]['piece']);
        $this->assertSame('RANDRIAMANANTENA', $details['pieces_prises'][1]['agent']);

        $contenu = $this->actingAs($utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Pièces réceptionnées')
            ->assertSee('Qui a pris les pièces reçues', false)
            ->assertSee('Voir les détails')
            ->assertSee("Détails de l'action", false)
            ->assertSee("N° d'enregistrement", false)
            ->assertSee('detail-action-'.$historique->id)
            ->assertSee('19/11/2026 à 14:07:09')
            ->assertSee('Acte de naissance')
            ->assertSee('RANDRIAMANANTENA')
            ->getContent();

        // Une seule ligne récapitulative et aucun bouton sur les lignes de pièces.
        $this->assertSame(1, substr_count($contenu, "'open-modal', 'detail-pieces-recues'"));
        $this->assertStringNotContainsString('detail-action-'.$piece1->id, $contenu);
        $this->assertStringNotContainsString('detail-action-'.$piece2->id, $contenu);
        $this->assertSame(2, substr_count($contenu, 'Voir les détails'));
    }

    public function test_les_details_rejete_et_incomplet_montrent_qui_a_pris_les_pieces_recues(): void
    {
        [$utilisateur, $division, $dossier] = $this->creerContexte();

        $this->ajouterHistorique($dossier, $utilisateur, [
            'type_action' => 'ajout_piece',
            'observation' => 'Pièce : Acte de naissance',
        ], '2026-11-18 09:00:00');

        foreach ([StatutDossier::Rejete, StatutDossier::Incomplet] as $statut) {
            $historique = $this->ajouterHistorique($dossier, $utilisateur, [
                'type_action' => 'changement_statut',
                'ancien_statut' => StatutDossier::Recu->value,
                'nouveau_statut' => $statut->value,
                'observation' => 'Motif : pièces non conformes.',
            ], '2026-11-19 14:07:09');

            $details = $historique->detailsTracabilite();

            $this->assertSame($statut->label(), $details['statut_apres']);
            $this->assertCount(1, $details['pieces_prises']);
            $this->assertSame('Acte de naissance', $details['pieces_prises'][0]['piece']);
        }

        $this->actingAs($utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Dossier déclaré', false)
            ->assertSee('Qui a pris les pièces reçues', false)
            ->assertSee('Acte de naissance');
    }
}
