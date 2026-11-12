<?php

namespace Tests\Feature;

use App\Enums\FamilleMouvement;
use App\Enums\RoleUtilisateur;
use App\Models\Division;
use App\Models\TypeDossier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ParametrageTypesDossiersTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $this->admin = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $this->division->id,
        ]);
    }

    private function insererSansCast(string $code, string $libelle, ?string $famille): void
    {
        DB::table('types_dossiers')->insert([
            'division_id' => $this->division->id,
            'code' => $code,
            'libelle' => $libelle,
            'famille' => $famille,
            'cree_le' => now(),
            'modifie_le' => now(),
        ]);
    }

    public function test_une_famille_inconnue_ne_provoque_plus_une_erreur_500(): void
    {
        // Valeurs héritées des anciens seeders, insérées en brut (sans le cast).
        $this->insererSansCast('09', 'Autres nominations', 'Création');
        $this->insererSansCast('V01', 'Renouvellement de contrat', 'Agent contractuel');

        $this->actingAs($this->admin)
            ->get(route('types-dossiers.index'))
            ->assertOk()
            ->assertSee('Autres nominations');

        // Un libellé historique est normalisé vers l'enum correspondant.
        $this->assertSame(
            FamilleMouvement::Creation,
            TypeDossier::where('code', '09')->firstOrFail()->famille
        );

        // Une valeur hors référentiel renvoie null au lieu de lever une exception.
        $this->assertNull(TypeDossier::where('code', 'V01')->firstOrFail()->famille);
    }

    public function test_une_famille_valide_est_enregistree(): void
    {
        $this->actingAs($this->admin)->post(route('types-dossiers.store'), [
            'division_id' => $this->division->id,
            'code' => '71',
            'libelle' => 'Changement de situation',
            'famille' => 'modification_situation',
        ])->assertRedirect(route('types-dossiers.index'));

        $this->assertDatabaseHas('types_dossiers', [
            'code' => '71',
            'famille' => 'modification_situation',
        ]);
    }
}
