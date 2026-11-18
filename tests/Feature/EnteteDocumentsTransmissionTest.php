<?php

namespace Tests\Feature;

use App\Enums\FamilleMouvement;
use App\Enums\PoleDossier;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauCreation;
use App\Models\BordereauRecu;
use App\Models\Destination;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnteteDocumentsTransmissionTest extends TestCase
{
    use RefreshDatabase;

    private const ENTETE = [
        'DIRECTION REGIONALE DU BUDGET ET DES FINANCES',
        "AMORON'I MANIA",
    ];

    private const REFERENCE = 'MEF/SG/DGBF/DSP/DRBF.AMM';

    private function administrateur(Division $division): User
    {
        return User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $division->id,
        ]);
    }

    private function typeDossier(Division $division): TypeDossier
    {
        return TypeDossier::create([
            'division_id' => $division->id,
            'code' => 'TEST',
            'libelle' => 'Changement de situation',
            'famille' => FamilleMouvement::ModificationSituation,
        ]);
    }

    private function bordereauCreation(Division $division, User $utilisateur): BordereauCreation
    {
        return BordereauCreation::create([
            'division_id' => $division->id,
            'lieu_id' => Lieu::create(['code' => '101', 'nom' => 'Ambositra', 'sigle' => 'AMM'])->id,
            'destination_id' => Destination::create(['nom' => 'Direction Generale'])->id,
            'utilisateur_id' => $utilisateur->id,
            'chapitre' => '4200',
            'annee' => 2026,
            'num_ordre' => 1,
            'date_edition' => '2026-10-15',
        ]);
    }

    public function test_la_zone_dentete_et_la_reference_sont_uniques_pour_les_deux_divisions(): void
    {
        $this->assertSame(self::ENTETE, config('soit_transmis.entete_administration'));
        $this->assertSame(self::REFERENCE, config('soit_transmis.reference_suffixe'));
    }

    public function test_le_bordereau_denvoi_solde_affiche_la_nouvelle_entete_et_reference(): void
    {
        $division = Division::create(['nom' => Division::NOM_SOLDE]);
        $utilisateur = $this->administrateur($division);
        $bordereau = $this->bordereauCreation($division, $utilisateur);

        $this->actingAs($utilisateur)
            ->get(route('bordereaux-creation.imprimer', $bordereau))
            ->assertOk()
            ->assertSee(self::ENTETE[0])
            ->assertSee(self::ENTETE[1])
            ->assertSee('2026/'.self::REFERENCE)
            ->assertDontSee('SRSP.AMM.SOLDE')
            ->assertDontSee("Service Régional de la Solde et des Pensions Amoron'i Mania");
    }

    public function test_la_note_soit_transmis_solde_affiche_la_nouvelle_entete(): void
    {
        $division = Division::create(['nom' => Division::NOM_SOLDE]);
        $utilisateur = $this->administrateur($division);
        $bordereau = $this->bordereauCreation($division, $utilisateur);

        $this->actingAs($utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->assertSee(self::ENTETE[0])
            ->assertSee(self::ENTETE[1])
            ->assertDontSee('SERVICE REGIONAL DE LA SOLDE ET DES PENSIONS<br>');
    }

    public function test_le_cachet_du_bordereau_recu_utilise_aussi_la_zone(): void
    {
        $division = Division::create(['nom' => Division::NOM_SOLDE]);
        $utilisateur = $this->administrateur($division);
        $type = $this->typeDossier($division);

        $bordereau = BordereauRecu::create([
            'division_id' => $division->id,
            'service_origine_id' => ServiceOrigine::create(['nom' => 'DSP'])->id,
            'utilisateur_id' => $utilisateur->id,
            'reference' => 'REF-2026/001',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
        ]);

        Dossier::create([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $division->id,
            'agent_id' => Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean'])->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $utilisateur->id,
            'statut' => StatutDossier::Recu,
            'pole' => PoleDossier::Solde,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('bordereaux.imprimer', $bordereau))
            ->assertOk()
            ->assertSee(self::ENTETE[0])
            ->assertSee(self::ENTETE[1])
            ->assertDontSee('Service régional de la Solde');
    }

    public function test_la_note_soit_transmis_visa_affiche_la_nouvelle_entete_et_reference(): void
    {
        $division = Division::create(['nom' => Division::NOM_VISA]);
        $utilisateur = $this->administrateur($division);
        $type = $this->typeDossier($division);

        $bordereau = BordereauRecu::create([
            'division_id' => $division->id,
            'service_origine_id' => ServiceOrigine::create(['nom' => 'DSP'])->id,
            'lieu_id' => Lieu::create(['code' => '101', 'nom' => 'Ambositra', 'sigle' => 'AMM'])->id,
            'utilisateur_id' => $utilisateur->id,
            'reference' => 'REF-2026/001',
            'objet' => 'POUR VISA',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
        ]);

        Dossier::create([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $division->id,
            'agent_id' => Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean'])->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $utilisateur->id,
            'statut' => StatutDossier::Traite,
            'pole' => PoleDossier::Visa,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('bordereaux.soit-transmis-visa', $bordereau))
            ->assertOk()
            ->assertSee(self::ENTETE[0])
            ->assertSee(self::ENTETE[1])
            ->assertSee(now()->year.'/'.self::REFERENCE)
            ->assertDontSee('/MEF/SG/DGBF/DSP/SRSP.AMM');
    }
}
