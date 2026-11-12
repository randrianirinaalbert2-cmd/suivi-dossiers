<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Codes mouvements relevés sur la liste papier du service et absents du seeder
 * principal (09, 18 et 50).
 */
class CodesComplementairesSeeder extends Seeder
{
    public function run(): void
    {
        $maintenant = now();
        $soldeId = DB::table('divisions')->where('nom', 'Solde')->value('id');
        if (! $soldeId) {
            return;
        }
        $codes = [
            ['09', 'Autres nominations', 'creation'],
            ['18', 'Bonification d\'ancienneté', 'modification_situation'],
            ['50', 'Indemnité d\'installation de retraite', null],
        ];
        foreach ($codes as [$code, $libelle, $famille]) {
            $existe = DB::table('types_dossiers')->where('division_id', $soldeId)->where('code', $code)->exists();
            if ($existe) {
                continue;
            }
            DB::table('types_dossiers')->insert([
                'division_id' => $soldeId,
                'code' => $code,
                'libelle' => $libelle,
                'famille' => $famille,
                'cree_le' => $maintenant,
                'modifie_le' => $maintenant,
            ]);
        }
    }
}
