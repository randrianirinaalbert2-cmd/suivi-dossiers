<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\ServiceOrigine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BordereauRecuServiceOrigineTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private User $utilisateur;

    private ServiceOrigine $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $this->service = ServiceOrigine::create(['nom' => 'DSP']);
        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $this->division->id,
        ]);
    }

    private function payload(array $surcharge = []): array
    {
        return array_merge([
            'division_id' => $this->division->id,
            'service_origine_id' => (string) $this->service->id,
            'reference' => 'REF-2026/001',
            'date_bordereau' => '2026-10-15',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-15',
        ], $surcharge);
    }

    public function test_saisie_dun_nouveau_service_a_la_volee(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'service_origine_id' => '__autre__',
            'nouveau_service_origine' => 'SRSP Amoron\'i Mania',
        ]));

        $bordereau = BordereauRecu::firstOrFail();
        $service = ServiceOrigine::where('nom', 'SRSP Amoron\'i Mania')->firstOrFail();

        $this->assertSame($service->id, (int) $bordereau->service_origine_id);
    }

    public function test_le_nouveau_service_est_reutilisable_au_formulaire(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'service_origine_id' => '__autre__',
            'nouveau_service_origine' => 'Direction des finances',
        ]));

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.create'))
            ->assertOk()
            ->assertSee('Direction des finances')
            ->assertSee('Autre (nouveau service)');
    }

    public function test_option_autre_sans_nom_provoque_une_erreur(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'service_origine_id' => '__autre__',
            'nouveau_service_origine' => ' ',
        ]))->assertSessionHasErrors('nouveau_service_origine');

        $this->assertDatabaseCount('bordereaux_recus', 0);
    }

    public function test_aucun_service_choisi_provoque_une_erreur(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'service_origine_id' => '',
        ]))->assertSessionHasErrors('service_origine_id');
    }

    public function test_un_service_existant_nest_pas_duplique(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'service_origine_id' => '__autre__',
            'nouveau_service_origine' => 'DSP',
        ]));

        $this->assertDatabaseCount('services_origine', 1);
        $this->assertSame($this->service->id, (int) BordereauRecu::firstOrFail()->service_origine_id);
    }
}
