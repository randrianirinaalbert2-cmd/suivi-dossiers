<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('im', 10)->unique();
            $table->string('nom', 100);
            $table->string('prenoms', 150)->nullable();
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();

            $table->index('nom');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
