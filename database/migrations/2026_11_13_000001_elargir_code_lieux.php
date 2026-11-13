<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le code d'une localité n'est plus limité à 3 caractères : certaines
     * localités génériques (ex. « Autre Min » pour les autres ministères)
     * ont besoin d'un libellé plus long.
     */
    public function up(): void
    {
        Schema::table('lieux', function (Blueprint $table) {
            $table->string('code', 30)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lieux', function (Blueprint $table) {
            $table->string('code', 3)->change();
        });
    }
};
