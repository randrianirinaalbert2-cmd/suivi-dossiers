<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\TypeDossier;
use App\Models\TypePiece;
use Database\Seeders\PiecesSoldeOfficiellesSeeder;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class PiecesSoldeOfficiellesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liste_officielle_est_appliquee_aux_types_de_solde(): void
    {
        $this->seed(ReferentielSeeder::class);
        $this->seed(PiecesSoldeOfficiellesSeeder::class);

        $division = Division::where('nom', 'Solde')->firstOrFail();
        $officielles = (new ReflectionClass(PiecesSoldeOfficiellesSeeder::class))->getConstant('PIECES');

        foreach ($officielles as $code => $pieces) {
            $type = TypeDossier::where('division_id', $division->id)->where('code', $code)->firstOrFail();

            $obtenu = $type->piecesRequises()
                ->with('typePiece')
                ->get()
                ->map(fn ($piece) => [$piece->typePiece?->libelle, $piece->nb_exemplaires])
                ->sortBy(fn ($piece) => $piece[0])
                ->values()
                ->all();

            $attendu = collect($pieces)
                ->sortBy(fn ($piece) => $piece[0])
                ->values()
                ->all();

            $this->assertSame($attendu, $obtenu, "Pièces du code {$code}");
        }
    }

    public function test_les_pieces_hors_liste_officielle_sont_retirees(): void
    {
        $this->seed(ReferentielSeeder::class);

        $division = Division::where('nom', 'Solde')->firstOrFail();
        $type = TypeDossier::where('division_id', $division->id)->where('code', '02')->firstOrFail();
        $piece = TypePiece::firstOrCreate(['libelle' => 'Pièce ajoutée par le service']);
        $type->typesPieces()->attach($piece->id, ['nb_exemplaires' => 5]);

        $this->seed(PiecesSoldeOfficiellesSeeder::class);

        $this->assertDatabaseMissing('pieces_requises', [
            'type_dossier_id' => $type->id,
            'type_piece_id' => $piece->id,
        ]);
        $this->assertSame(3, $type->piecesRequises()->count());
    }

    public function test_les_codes_absents_de_la_liste_officielle_ne_sont_pas_modifies(): void
    {
        $this->seed(ReferentielSeeder::class);

        $division = Division::where('nom', 'Solde')->firstOrFail();
        $type = TypeDossier::where('division_id', $division->id)->where('code', '21')->firstOrFail();
        $piece = TypePiece::firstOrCreate(['libelle' => 'Pièce du service hors liste']);
        $type->typesPieces()->attach($piece->id, ['nb_exemplaires' => 4]);
        $avant = $type->piecesRequises()->count();

        $this->seed(PiecesSoldeOfficiellesSeeder::class);

        $this->assertSame($avant, $type->piecesRequises()->count());
        $this->assertDatabaseHas('pieces_requises', [
            'type_dossier_id' => $type->id,
            'type_piece_id' => $piece->id,
            'nb_exemplaires' => 4,
        ]);
    }
}
