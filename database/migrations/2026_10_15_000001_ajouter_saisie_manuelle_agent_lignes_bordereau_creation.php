<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'lignes_bordereau_creation';

        $aFk = fn (string $nom): bool => collect(Schema::getForeignKeys($table))
            ->contains(fn (array $fk) => $fk['name'] === $nom);

        $aIndex = fn (string $nom): bool => collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => $index['name'] === $nom);

        // 1. Retirer les clés étrangères qui s'appuient sur l'index unique.
        if ($aFk('lignes_bordereau_creation_agent_id_foreign')) {
            Schema::table($table, fn (Blueprint $b) => $b->dropForeign(['agent_id']));
        }
        if ($aFk('lignes_bordereau_creation_bordereau_creation_id_foreign')) {
            Schema::table($table, fn (Blueprint $b) => $b->dropForeign(['bordereau_creation_id']));
        }

        // 2. Retirer l'index unique (bordereau, agent, type) devenu obsolète.
        if ($aIndex('lignes_bordereau_unique')) {
            Schema::table($table, fn (Blueprint $b) => $b->dropUnique('lignes_bordereau_unique'));
        }

        // 3. Rendre agent_id facultatif et ajouter la saisie manuelle de l'agent.
        Schema::table($table, function (Blueprint $b) {
            $b->unsignedBigInteger('agent_id')->nullable()->change();
            if (! Schema::hasColumn('lignes_bordereau_creation', 'agent_im')) {
                $b->string('agent_im', 30)->nullable()->after('agent_id');
            }
            if (! Schema::hasColumn('lignes_bordereau_creation', 'agent_nom')) {
                $b->string('agent_nom', 100)->nullable()->after('agent_im');
            }
            if (! Schema::hasColumn('lignes_bordereau_creation', 'agent_prenoms')) {
                $b->string('agent_prenoms', 150)->nullable()->after('agent_nom');
            }
        });

        // 4. Réajouter les clés étrangères (agent_id facultatif).
        Schema::table($table, function (Blueprint $b) use ($aFk) {
            if (! $aFk('lignes_bordereau_creation_bordereau_creation_id_foreign')) {
                $b->foreign('bordereau_creation_id')->references('id')->on('bordereaux_creation')->cascadeOnDelete();
            }
            if (! $aFk('lignes_bordereau_creation_agent_id_foreign')) {
                $b->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        $table = 'lignes_bordereau_creation';

        Schema::table($table, function (Blueprint $b) {
            $b->dropForeign(['agent_id']);
            $b->dropColumn(['agent_im', 'agent_nom', 'agent_prenoms']);
        });

        Schema::table($table, function (Blueprint $b) {
            $b->unsignedBigInteger('agent_id')->nullable(false)->change();
            $b->foreign('agent_id')->references('id')->on('agents')->restrictOnDelete();
        });
    }
};
