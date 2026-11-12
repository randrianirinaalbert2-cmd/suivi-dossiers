<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces_recues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->restrictOnDelete();
            $table->foreignId('type_piece_id')->constrained('types_pieces')->restrictOnDelete();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->unique(['dossier_id', 'type_piece_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_recues');
    }
};
