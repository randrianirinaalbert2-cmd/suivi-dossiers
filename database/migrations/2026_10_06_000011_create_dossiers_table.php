<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bordereau_recu_id')->constrained('bordereaux_recus')->restrictOnDelete();
            $table->foreignId('division_id')->constrained('divisions')->restrictOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->restrictOnDelete();
            $table->foreignId('type_dossier_id')->constrained('types_dossiers')->restrictOnDelete();
            $table->foreignId('responsable_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->enum('statut', [
                'recu',
                'en_traitement',
                'traite',
                'verifie',
                'transmis',
                'archive',
                'incomplet',
                'rejete',
            ])->default('recu');
            $table->text('observation')->nullable();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->index(['agent_id', 'type_dossier_id']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
