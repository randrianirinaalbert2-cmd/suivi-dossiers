<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bordereaux_recus', function (Blueprint $table) {
            $table->foreignId('lieu_id')
                ->nullable()
                ->after('service_origine_id')
                ->constrained('lieux')
                ->nullOnDelete();

            $table->unsignedSmallInteger('nombre_dossiers_attendus')
                ->nullable()
                ->after('observation')
                ->comment('Nombre de dossiers portés sur le bordereau papier');
        });
    }

    public function down(): void
    {
        Schema::table('bordereaux_recus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lieu_id');
            $table->dropColumn('nombre_dossiers_attendus');
        });
    }
};
