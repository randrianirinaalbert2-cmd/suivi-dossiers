<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bordereaux_recus', function (Blueprint $table) {
            $table->string('objet', 200)->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('bordereaux_recus', function (Blueprint $table) {
            $table->dropColumn('objet');
        });
    }
};
