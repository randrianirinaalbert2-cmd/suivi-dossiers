<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bordereaux_recus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->restrictOnDelete();
            $table->foreignId('service_origine_id')->constrained('services_origine')->restrictOnDelete();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->restrictOnDelete();
            $table->string('reference', 80);
            $table->date('date_bordereau');
            $table->string('num_arrivee', 10);
            $table->date('date_arrivee');
            $table->string('observation', 255)->nullable();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->unique(['service_origine_id', 'reference']);
            $table->index('num_arrivee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereaux_recus');
    }
};
