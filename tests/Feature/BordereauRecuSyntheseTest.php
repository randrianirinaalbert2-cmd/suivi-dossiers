<?php

namespace Tests\Feature;

use App\Enums\RoleUtilisateur;
use App\Models\BordereauRecu;
use App\Models\Division;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BordereauRecuSyntheseTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private User $utilisateur;

    private Lieu $lieu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $this->lieu = Lieu::create(['code' => '304', 'sigle' => 'AMB', 'nom' => 'Ambositra']);
        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
        ]);
    }

    private function bordereau(array $surcharge = []): BordereauRecu
    {
        return BordereauRecu::create(array_merge([
            'division_id' => $this->division->id,
            'service_origine_id' => ServiceOrigine::create(['nom' => 'DREN'])->id,
            'lieu_id' => $this->lieu->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/001',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
            'nombre_dossiers_attendus' => 16,
        ], $surcharge));
    }

    public function test_le_formulaire_propose_la_localite_et_le_nombre_de_dossiers(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.create'))
            ->assertOk()
            ->assertSee('Localité')
            ->assertSee('304 — Ambositra')
            ->assertSee('Nombre de dossiers annoncés');
    }

    public function test_la_fiche_affiche_la_localite_et_le_nombre_annonce(): void
    {
        $bordereau = $this->bordereau();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertSee('304 — Ambositra')
            ->assertSee('16');
    }

    public function test_une_alerte_apparait_quand_le_compte_ne_correspond_pas(): void
    {
        $bordereau = $this->bordereau();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertSee('Vérifiez qu\'aucun dossier n\'a été oublié', false);
    }

    public function test_aucune_alerte_quand_le_compte_est_conforme(): void
    {
        $bordereau = $this->bordereau(['nombre_dossiers_attendus' => 0]);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertDontSee('Vérifiez qu\'aucun dossier n\'a été oublié', false);
    }

    public function test_aucune_alerte_sans_nombre_annonce(): void
    {
        $bordereau = $this->bordereau(['nombre_dossiers_attendus' => null]);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertDontSee('Vérifiez qu\'aucun dossier n\'a été oublié', false);
    }

    public function test_le_bordereau_d_envoi_affiche_la_localite(): void
    {
        $bordereau = $this->bordereau();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.imprimer', $bordereau))
            ->assertOk()
            ->assertSee('LOCALITE : 304')
            ->assertSee("BORDEREAU D'ENVOI", false);
    }
}
