<?php

use App\Enums\FamilleMouvement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les seeders historiques (codes complémentaires, visa) ont écrit des
     * libellés ou des familles hors référentiel dans `types_dossiers.famille`.
     * Le cast enum échouait alors et provoquait une erreur 500 sur la page
     * Paramétrage. On normalise : libellés -> valeurs d'enum, le reste -> null.
     */
    public function up(): void
    {
        foreach (FamilleMouvement::cases() as $famille) {
            DB::table('types_dossiers')
                ->where('famille', $famille->label())
                ->update(['famille' => $famille->value]);
        }

        DB::table('types_dossiers')
            ->whereNotNull('famille')
            ->whereNotIn('famille', FamilleMouvement::valeurs())
            ->update(['famille' => null]);
    }

    public function down(): void
    {
        // Irréversible : les valeurs non conformes d'origine ne sont pas restaurables.
    }
};
