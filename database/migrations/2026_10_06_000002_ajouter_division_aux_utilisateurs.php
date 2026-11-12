<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->foreignId('division_id')
                ->nullable()
                ->after('role')
                ->constrained('divisions')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
        });
    }
};
