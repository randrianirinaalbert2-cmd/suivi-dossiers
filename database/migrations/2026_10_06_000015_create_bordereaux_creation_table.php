<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bordereaux_creation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->restrictOnDelete();
            $table->foreignId('lieu_id')->constrained('lieux')->restrictOnDelete();
            $table->foreignId('destination_id')->constrained('destinations')->restrictOnDelete();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->string('chapitre', 6);
            $table->string('paquet', 10)->nullable();
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('num_ordre');
            $table->date('date_edition')->nullable();
            $table->string('observation', 255)->nullable();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->unique(['annee', 'num_ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereaux_creation');
    }
};
