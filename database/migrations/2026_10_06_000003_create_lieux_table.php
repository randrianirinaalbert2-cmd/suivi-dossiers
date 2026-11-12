<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieux', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('nom', 100);
            $table->string('sigle', 3);
            $table->timestamp('cree_le')->nullable();
            $table->timestamp('modifie_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieux');
    }
};
