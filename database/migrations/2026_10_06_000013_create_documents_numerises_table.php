<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents_numerises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bordereau_recu_id')->nullable()->constrained('bordereaux_recus')->restrictOnDelete();
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers')->restrictOnDelete();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->string('type_document', 30)->default('bordereau');
            $table->string('nom_fichier', 255);
            $table->string('chemin_fichier', 255);
            $table->string('empreinte_fichier', 64)->unique();
            $table->unsignedBigInteger('taille_fichier');
            $table->timestamp('date_numerisation')->useCurrent();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_numerises');
    }
};
