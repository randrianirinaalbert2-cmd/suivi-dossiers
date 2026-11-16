<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('dossiers', 'avec_rappel')) {
            return;
        }

        Schema::table('dossiers', function (Blueprint $table) {
            // Certains actes (intégration, renouvellement de contrat) exigent
            // deux exemplaires supplémentaires de l'acte formaté lorsqu'ils
            // comportent un rappel de solde.
            $table->boolean('avec_rappel')->default(false)->after('passe_par_visa');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('dossiers', 'avec_rappel')) {
            return;
        }

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('avec_rappel');
        });
    }
};
