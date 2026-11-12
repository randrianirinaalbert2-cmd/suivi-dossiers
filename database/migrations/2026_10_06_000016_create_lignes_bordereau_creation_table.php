<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_bordereau_creation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bordereau_creation_id')->constrained('bordereaux_creation')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('type_dossier_id')->constrained('types_dossiers')->restrictOnDelete();
            $table->string('observation', 255)->nullable();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->unique(['bordereau_creation_id', 'agent_id', 'type_dossier_id'], 'lignes_bordereau_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_bordereau_creation');
    }
};
