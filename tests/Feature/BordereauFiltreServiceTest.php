<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\ServiceOrigine;
use App\Models\User;
use Database\Seeders\ServicesOrigineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BordereauFiltreServiceTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private User $utilisateur;

    private ServiceOrigine $presidence;

    private ServiceOrigine $sante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
        ]);

        $this->presidence = ServiceOrigine::create(['nom' => '01 / PRÉSIDENCE DE LA RÉPUBLIQUE']);
        $this->sante = ServiceOrigine::create(['nom' => '71 / MINISTÈRE DE LA SANTÉ PUBLIQUE']);
    }

    private function bordereau(ServiceOrigine $service, string $reference, string $numero): BordereauRecu
    {
        return BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => $reference,
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-11-16',
            'num_arrivee' => $numero,
            'date_arrivee' => '2026-11-16',
        ]);
    }

    public function test_le_filtre_service_est_une_zone_de_recherche(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.index'))
            ->assertOk()
            ->assertSee('service_origine_id_recherche')
            ->assertSee('serviceCombobox')
            ->assertSee('Tous');
    }

    public function test_le_filtre_par_service_ne_montre_que_les_bordereaux_du_service(): void
    {
        $this->bordereau($this->presidence, 'REF-PRES-001', '101');
        $this->bordereau($this->sante, 'REF-SANTE-001', '102');

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.index', ['service_origine_id' => $this->presidence->id]))
            ->assertOk()
            ->assertSee('REF-PRES-001')
            ->assertDontSee('REF-SANTE-001');
    }

    public function test_le_seeder_ajoute_toute_la_liste_des_ministeres(): void
    {
        $this->seed(ServicesOrigineSeeder::class);

        $this->assertDatabaseHas('services_origine', [
            'nom' => '21 / MINISTÈRE DE L\'ÉCONOMIE ET DES FINANCES',
        ]);
        $this->assertDatabaseHas('services_origine', [
            'nom' => '93 / HAUTE COUR DE JUSTICE',
        ]);
        $this->assertSame(0, ServiceOrigine::where('nom', 'like', '42 /%')->count());

        // Idempotent : une deuxième exécution ne crée aucun doublon.
        $total = ServiceOrigine::count();
        $this->seed(ServicesOrigineSeeder::class);
        $this->assertSame($total, ServiceOrigine::count());
        $this->assertSame(52, $total);
    }
}
