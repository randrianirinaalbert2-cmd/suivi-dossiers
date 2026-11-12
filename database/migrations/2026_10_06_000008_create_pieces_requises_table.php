<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pieces_requises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_dossier_id')->constrained('types_dossiers')->restrictOnDelete();
            $table->foreignId('type_piece_id')->constrained('types_pieces')->restrictOnDelete();
            $table->unsignedTinyInteger('nb_exemplaires')->default(1);
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->unique(['type_dossier_id', 'type_piece_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_requises');
    }
};
