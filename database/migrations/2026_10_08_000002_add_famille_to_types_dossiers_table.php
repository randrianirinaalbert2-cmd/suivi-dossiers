<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('types_dossiers', function (Blueprint $table) {
            $table->string('famille', 40)->nullable()->after('libelle');
            $table->index('famille');
        });
    }

    public function down(): void
    {
        Schema::table('types_dossiers', function (Blueprint $table) {
            $table->dropIndex(['famille']);
            $table->dropColumn('famille');
        });
    }
};
