<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bordereaux_creation', function (Blueprint $table) {
            if (! Schema::hasColumn('bordereaux_creation', 'statut')) {
                $table->string('statut', 20)->default('valide')->after('observation');
            }
            if (! Schema::hasColumn('bordereaux_creation', 'archive_le')) {
                $table->timestamp('archive_le')->nullable()->after('statut');
            }
            if (! Schema::hasColumn('bordereaux_creation', 'archive_par_id')) {
                $table->foreignId('archive_par_id')->nullable()->after('archive_le')
                    ->constrained('utilisateurs')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bordereaux_creation', function (Blueprint $table) {
            if (Schema::hasColumn('bordereaux_creation', 'archive_par_id')) {
                $table->dropForeign(['archive_par_id']);
                $table->dropColumn('archive_par_id');
            }
            foreach (['archive_le', 'statut'] as $col) {
                if (Schema::hasColumn('bordereaux_creation', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
