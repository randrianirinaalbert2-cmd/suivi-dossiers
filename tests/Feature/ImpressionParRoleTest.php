<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\BordereauCreation;
use App\Models\BordereauRecu;
use App\Models\Destination;
use App\Models\Division;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Chaque responsable doit pouvoir imprimer le bordereau de sa division.
 */
class ImpressionParRoleTest extends TestCase
{
    use RefreshDatabase;

    private Division $solde;

    private Division $visa;

    private Lieu $lieu;

    private Destination $destination;

    private ServiceOrigine $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->solde = Division::create(['nom' => 'Solde']);
        $this->visa = Division::create(['nom' => 'Visa']);
        $this->lieu = Lieu::create(['code' => '101', 'nom' => 'Ambositra', 'sigle' => 'AMM']);
        $this->destination = Destination::create(['nom' => 'Direction Generale']);
        $this->service = ServiceOrigine::create(['nom' => 'DSP']);
    }

    private function utilisateur(RoleUtilisateur $role, ?Division $division): User
    {
        return User::factory()->create([
            'role' => $role,
            'division_id' => $division?->id,
        ]);
    }

    private function bordereauRecu(Division $division, User $auteur, string $reference): BordereauRecu
    {
        return BordereauRecu::create([
            'division_id' => $division->id,
            'service_origine_id' => $this->service->id,
            'lieu_id' => $this->lieu->id,
            'utilisateur_id' => $auteur->id,
            'reference' => $reference,
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-01',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-01',
        ]);
    }

    private function bordereauCreation(Division $division, User $auteur): BordereauCreation
    {
        return BordereauCreation::create([
            'division_id' => $division->id,
            'lieu_id' => $this->lieu->id,
            'destination_id' => $this->destination->id,
            'utilisateur_id' => $auteur->id,
            'chapitre' => '4200',
            'paquet' => '01',
            'annee' => 2026,
            'num_ordre' => 1,
            'statut' => 'valide',
        ]);
    }

    public function test_le_responsable_imprime_son_bordereau_recu(): void
    {
        $responsable = $this->utilisateur(RoleUtilisateur::Responsable, $this->solde);
        $bordereau = $this->bordereauRecu($this->solde, $responsable, 'REF-SOLDE');

        $this->actingAs($responsable)
            ->get(route('bordereaux.imprimer', $bordereau))
            ->assertOk();
    }

    public function test_le_responsable_visa_imprime_son_bordereau_recu(): void
    {
        $responsable = $this->utilisateur(RoleUtilisateur::Responsable, $this->visa);
        $bordereau = $this->bordereauRecu($this->visa, $responsable, 'REF-VISA');

        $this->actingAs($responsable)
            ->get(route('bordereaux.imprimer', $bordereau))
            ->assertOk();
    }

    public function test_le_responsable_imprime_le_bordereau_de_creation_de_sa_division(): void
    {
        $responsable = $this->utilisateur(RoleUtilisateur::Responsable, $this->solde);
        $bordereau = $this->bordereauCreation($this->solde, $responsable);

        $this->actingAs($responsable)
            ->get(route('bordereaux-creation.imprimer', $bordereau))
            ->assertOk();
    }

    public function test_le_chef_de_division_imprime_le_bordereau_de_creation(): void
    {
        $chef = $this->utilisateur(RoleUtilisateur::ChefDivision, $this->solde);
        $bordereau = $this->bordereauCreation($this->solde, $chef);

        $this->actingAs($chef)
            ->get(route('bordereaux-creation.imprimer', $bordereau))
            ->assertOk();
    }

    public function test_l_administrateur_imprime_tout(): void
    {
        $admin = $this->utilisateur(RoleUtilisateur::Administrateur, null);
        $responsable = $this->utilisateur(RoleUtilisateur::Responsable, $this->solde);
        $recu = $this->bordereauRecu($this->solde, $responsable, 'REF-SOLDE');
        $creation = $this->bordereauCreation($this->solde, $responsable);

        $this->actingAs($admin)->get(route('bordereaux.imprimer', $recu))->assertOk();
        $this->actingAs($admin)->get(route('bordereaux-creation.imprimer', $creation))->assertOk();
    }

    public function test_le_responsable_n_imprime_pas_le_bordereau_d_une_autre_division(): void
    {
        $responsable = $this->utilisateur(RoleUtilisateur::Responsable, $this->visa);
        $autre = $this->utilisateur(RoleUtilisateur::Responsable, $this->solde);
        $bordereau = $this->bordereauRecu($this->solde, $autre, 'REF-SOLDE');

        $this->actingAs($responsable)
            ->get(route('bordereaux.imprimer', $bordereau))
            ->assertForbidden();
    }

    public function test_le_bouton_imprimer_est_visible_pour_le_responsable(): void
    {
        $responsable = $this->utilisateur(RoleUtilisateur::Responsable, $this->solde);
        $bordereau = $this->bordereauRecu($this->solde, $responsable, 'REF-SOLDE');

        $this->actingAs($responsable)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertSee(route('bordereaux.imprimer', $bordereau), false);
    }
}
