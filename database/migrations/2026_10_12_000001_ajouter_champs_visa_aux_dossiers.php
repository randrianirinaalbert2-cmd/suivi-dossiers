<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            if (! Schema::hasColumn('dossiers', 'reference_acte')) {
                $table->string('reference_acte', 60)->nullable()->after('statut');
            }
            if (! Schema::hasColumn('dossiers', 'date_acte')) {
                $table->date('date_acte')->nullable()->after('reference_acte');
            }
            if (! Schema::hasColumn('dossiers', 'num_visa')) {
                $table->string('num_visa', 20)->nullable()->after('date_acte');
            }
            if (! Schema::hasColumn('dossiers', 'date_visa')) {
                $table->date('date_visa')->nullable()->after('num_visa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $columns = ['reference_acte', 'date_acte', 'num_visa', 'date_visa'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('dossiers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
