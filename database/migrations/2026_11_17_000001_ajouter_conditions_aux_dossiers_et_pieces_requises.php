<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conditions de dossier et pièces conditionnelles.
 *
 * Certaines pièces ne sont exigées que dans une situation précise :
 *  - renouvellement de contrat (17) : attestation de non interruption de
 *    service si le contrat a été interrompu plus de trois mois ;
 *  - allocations familiales (60) : pièces liées aux enfants, au mariage ou
 *    au célibat, et à l'intégration antérieure d'un enfant (CNAPS).
 *
 * La condition est portée par la pièce exigée, la situation par le dossier :
 * le nombre d'exemplaires peut aussi changer lorsqu'une condition est vraie
 * (acte formaté à 4 exemplaires pour un enfant de moins de 4 ans).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->unsignedTinyInteger('nombre_enfants')->default(0)->after('avec_rappel');
            $table->boolean('enfant_moins_de_4_ans')->default(false)->after('nombre_enfants');
            $table->string('situation_familiale', 20)->nullable()->after('enfant_moins_de_4_ans');
            $table->boolean('interruption_contrat_plus_3_mois')->default(false)->after('situation_familiale');
            $table->boolean('enfant_deja_integre')->default(false)->after('interruption_contrat_plus_3_mois');
        });

        Schema::table('pieces_requises', function (Blueprint $table) {
            // Condition d'exigence : la pièce n'est demandée que si elle est vraie.
            $table->string('condition', 50)->nullable()->after('nb_exemplaires');
            // Nombre d'exemplaires alternatif et condition qui le déclenche.
            $table->unsignedTinyInteger('nb_exemplaires_condition')->nullable()->after('condition');
            $table->string('condition_exemplaires', 50)->nullable()->after('nb_exemplaires_condition');
            // Précision affichée au guichet (original, photocopie, « ou »...).
            $table->string('note', 150)->nullable()->after('condition_exemplaires');
        });
    }

    public function down(): void
    {
        Schema::table('pieces_requises', function (Blueprint $table) {
            $table->dropColumn(['condition', 'nb_exemplaires_condition', 'condition_exemplaires', 'note']);
        });

        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_enfants',
                'enfant_moins_de_4_ans',
                'situation_familiale',
                'interruption_contrat_plus_3_mois',
                'enfant_deja_integre',
            ]);
        });
    }
};
