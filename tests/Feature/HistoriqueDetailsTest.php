<?php

namespace Tests\Feature;

use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauRecu;
use App\Models\Division;
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

    public function test_le_bouton_voir_les_details_affiche_la_tracabilite_complete(): void
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

        // La date est la colonne created_at du modele et n'est pas assignable
        // en masse : on desactive les horodatages puis on l'affecte directement.
        $historique = Historique::withoutTimestamps(function () use ($dossier, $utilisateur) {
            $action = Historique::create([
                'dossier_id' => $dossier->id,
                'utilisateur_id' => $utilisateur->id,
                'type_action' => 'changement_statut',
                'ancien_statut' => StatutDossier::Recu->value,
                'nouveau_statut' => StatutDossier::EnTraitement->value,
                'observation' => 'Observation de tracabilite unique 12345',
            ]);
            $action->date_heure_action = '2026-11-19 14:07:09';
            $action->save();

            return $action;
        });

        $details = $historique->detailsTracabilite();

        $this->assertSame('Observation de tracabilite unique 12345', $details['observation']);
        $this->assertSame('19/11/2026 à 14:07:09', $details['date_heure']);
        $this->assertSame('admin@suivi.test', $details['courriel']);
        $this->assertSame('Reçu', $details['statut_avant']);
        $this->assertSame('En traitement', $details['statut_apres']);

        $this->actingAs($utilisateur)
            ->get(route('dossiers.show', $dossier))
            ->assertOk()
            ->assertSee('Voir les détails')
            ->assertSee("Détails de l'action", false)
            ->assertSee("N° d'enregistrement", false)
            ->assertSee('detail-action-'.$historique->id)
            ->assertSee('19/11/2026 à 14:07:09');
    }
}
