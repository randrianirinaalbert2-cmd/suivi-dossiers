<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->enum('pole', ['solde', 'visa'])->default('solde')->after('statut');
            $table->boolean('passe_par_visa')->default(false)->after('pole');
            $table->date('date_visa')->nullable()->after('passe_par_visa');

            $table->index('pole');
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropIndex(['pole']);
            $table->dropColumn(['pole', 'passe_par_visa', 'date_visa']);
        });
    }
};
