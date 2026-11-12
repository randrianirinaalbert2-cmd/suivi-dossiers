<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->restrictOnDelete();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->string('type_action', 30);
            $table->string('ancien_statut', 20)->nullable();
            $table->string('nouveau_statut', 20)->nullable();
            $table->text('observation')->nullable();
            $table->timestamp('date_heure_action')->useCurrent();

            $table->index(['dossier_id', 'date_heure_action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historiques');
    }
};
