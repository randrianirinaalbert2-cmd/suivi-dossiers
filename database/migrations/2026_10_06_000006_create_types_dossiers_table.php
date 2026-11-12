<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('types_dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->restrictOnDelete();
            $table->string('code', 10);
            $table->string('libelle', 100);
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->unique(['division_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('types_dossiers');
    }
};
