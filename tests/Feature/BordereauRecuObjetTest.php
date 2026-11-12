<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\ServiceOrigine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BordereauRecuObjetTest extends TestCase
{
    use RefreshDatabase;

    private Division $solde;

    private Division $visa;

    private ServiceOrigine $service;

    private User $utilisateur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->solde = Division::create(['nom' => 'Solde']);
        $this->visa = Division::create(['nom' => 'Visa']);
        $this->service = ServiceOrigine::create(['nom' => 'DSP']);
        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
        ]);
    }

    private function payload(array $surcharge = []): array
    {
        return array_merge([
            'division_id' => $this->solde->id,
            'service_origine_id' => (string) $this->service->id,
            'reference' => 'REF-2026/001',
            'date_bordereau' => '2026-10-19',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-19',
        ], $surcharge);
    }

    public function test_lobjet_dun_bordereau_solde_est_pour_mandatement(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload());

        $this->assertSame('POUR MANDATEMENT', BordereauRecu::firstOrFail()->objet);
    }

    public function test_lobjet_dun_bordereau_visa_est_pour_visa(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'division_id' => $this->visa->id,
            'reference' => 'REF-2026/003',
        ]));

        $this->assertSame('POUR VISA', BordereauRecu::firstOrFail()->objet);
    }

    public function test_lobjet_dune_autre_division_nest_pas_force(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'division_id' => $this->visa->id,
            'reference' => 'REF-2026/004',
            'objet' => null,
        ]));

        $this->assertSame('POUR VISA', BordereauRecu::firstOrFail()->objet);
    }

    public function test_un_objet_saisi_expressement_est_conserve(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'objet' => 'POUR RETRAITE',
        ]));

        $this->assertSame('POUR RETRAITE', BordereauRecu::firstOrFail()->objet);
    }

    public function test_un_objet_par_defaut_dune_autre_division_est_realigne_sur_la_division_choisie(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'division_id' => $this->visa->id,
            'reference' => 'REF-2026/005',
            'objet' => 'POUR MANDATEMENT',
        ]));

        $this->assertSame('POUR VISA', BordereauRecu::firstOrFail()->objet);
    }

    public function test_un_objet_par_defaut_de_la_division_choisie_est_conserve(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'division_id' => $this->visa->id,
            'reference' => 'REF-2026/006',
            'objet' => 'POUR VISA',
        ]));

        $this->assertSame('POUR VISA', BordereauRecu::firstOrFail()->objet);
    }

    public function test_un_objet_par_defaut_saisi_en_minuscules_est_realigne(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'division_id' => $this->visa->id,
            'reference' => 'REF-2026/007',
            'objet' => 'pour mandatement',
        ]));

        $this->assertSame('POUR VISA', BordereauRecu::firstOrFail()->objet);
    }

    public function test_un_bordereau_visa_ne_peut_jamais_porter_pour_mandatement(): void
    {
        $this->actingAs($this->utilisateur)->post(route('bordereaux.store'), $this->payload([
            'division_id' => $this->visa->id,
            'reference' => 'REF-2026/008',
            'objet' => 'POUR MANDATEMENT',
        ]));

        $bordereau = BordereauRecu::firstOrFail();
        $this->assertSame('POUR VISA', $bordereau->objet);
        $this->assertStringNotContainsString('MANDATEMENT', (string) $bordereau->objet);
    }

    public function test_le_champ_objet_est_verrouille_pour_une_division_avec_objet_par_defaut(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.create', ['division_id' => $this->visa->id]))
            ->assertOk()
            ->assertSee('x-bind:readonly="objetParDefaut !== \'\'"', false);
    }

    public function test_le_formulaire_naffiche_plus_de_placeholder_objet_coded_en_dur(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.create'))
            ->assertOk()
            ->assertDontSee('placeholder="POUR MANDATEMENT"', false);
    }

    public function test_le_formulaire_prefremplit_pour_la_division_solde(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.create', ['division_id' => $this->solde->id]))
            ->assertOk()
            ->assertSee('POUR MANDATEMENT', false);
    }

    public function test_le_formulaire_prefremplit_pour_la_division_visa(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.create', ['division_id' => $this->visa->id]))
            ->assertOk()
            ->assertSee('POUR VISA', false);
    }
}
