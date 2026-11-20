<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Copie la base MySQL locale vers la base Neon de Render.
 *
 * La source est la connexion `mysql` du .env local (installation actuelle).
 * La destination est la connexion `neon` (NEON_DATABASE_URL du .env local).
 * Aucune donnée locale n'est modifiée ou supprimée.
 */
class CopierDonneesVersNeon extends Command
{
    protected $signature = 'deploy:copier-donnees
                            {--forcer : Copier même si la destination contient déjà des utilisateurs}
                            {--sans-fichiers : Ne pas copier les pièces jointes et photos}';

    protected $description = 'Copier la base locale et les fichiers vers Neon pour le déploiement Render';

    /**
     * Ordre respectant les clés étrangères (parents avant enfants).
     * Les tables techniques (cache, jobs, sessions) ne sont pas copiées :
     * ce sont des données temporaires et des sessions actives.
     */
    private const TABLES = [
        'divisions',
        'lieux',
        'destinations',
        'services_origine',
        'types_dossiers',
        'types_pieces',
        'pieces_requises',
        'agents',
        'utilisateurs',
        'bordereaux_recus',
        'dossiers',
        'pieces_recues',
        'documents_numerises',
        'historiques',
        'bordereaux_creation',
        'lignes_bordereau_creation',
    ];

    public function handle(): int
    {
        $source = DB::connection('mysql');
        $cible = DB::connection('neon');

        try {
            $source->getPdo();
            $this->info('Connexion à la base locale MySQL : OK');
        } catch (Throwable $e) {
            $this->error('Base locale MySQL inaccessible : '.$e->getMessage());

            return self::FAILURE;
        }

        try {
            $cible->getPdo();
            $this->info('Connexion à la base Neon : OK');
        } catch (Throwable $e) {
            $this->error('Base Neon inaccessible : '.$e->getMessage());

            return self::FAILURE;
        }

        $tablesManquantes = collect(self::TABLES)
            ->reject(fn (string $table) => Schema::connection('neon')->hasTable($table))
            ->values();

        if ($tablesManquantes->isNotEmpty()) {
            $this->error('Schéma Neon incomplet. Lancez d’abord : php artisan migrate --database=neon --force');
            $this->error('Tables manquantes : '.$tablesManquantes->implode(', '));

            return self::FAILURE;
        }

        $dejaRemplie = $cible->table('utilisateurs')->count() > 0;

        if ($dejaRemplie && ! $this->option('forcer')) {
            $this->error('La base Neon contient déjà des utilisateurs. Arrêt pour éviter les doublons.');
            $this->error('Utilisez --forcer uniquement si vous savez ce que vous faites.');

            return self::FAILURE;
        }

        $this->warn('La base locale ne sera ni modifiée ni supprimée.');

        if (! $this->confirm('Lancer la copie vers Neon ?', true)) {
            return self::FAILURE;
        }

        $cible->beginTransaction();

        try {
            foreach (self::TABLES as $table) {
                $this->copierTable($table, $source, $cible);
            }

            if (! $this->option('sans-fichiers')) {
                $this->copierFichiers($source);
            }

            $this->reinitialiserSequences($cible);
            $cible->commit();
        } catch (Throwable $e) {
            $cible->rollBack();
            $this->error('Copie interrompue : '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Copie terminée. Vérifiez les comptes, dossiers et pièces sur Render.');

        return self::SUCCESS;
    }

    protected function copierTable(string $table, $source, $cible): void
    {
        $colonnesSource = Schema::connection('mysql')->getColumnListing($table);
        $definitions = collect(Schema::connection('neon')->getColumns($table))->keyBy('name');
        $colonnes = array_values(array_intersect($colonnesSource, $definitions->keys()->all()));

        if ($colonnes === []) {
            throw new \RuntimeException("Aucune colonne commune pour la table {$table}");
        }

        $cleTri = in_array('id', $colonnes, true) ? 'id' : $colonnes[0];
        $total = (int) $source->table($table)->count();
        $copiees = 0;

        $source->table($table)->orderBy($cleTri)->chunk(100, function ($lignes) use (
            $table, $cible, $colonnes, $definitions, &$copiees
        ) {
            $insert = $lignes->map(function ($ligne) use ($colonnes, $definitions) {
                $enregistrement = [];

                foreach ($colonnes as $colonne) {
                    $valeur = $ligne->{$colonne};
                    $type = strtolower((string) ($definitions->get($colonne)['type_name'] ?? ''));

                    if ($valeur !== null && in_array($type, ['bool', 'boolean', 'tinyint'], true)) {
                        $valeur = (bool) $valeur;
                    }

                    $enregistrement[$colonne] = $valeur;
                }

                return $enregistrement;
            })->all();

            $cible->table($table)->insert($insert);
            $copiees += count($insert);
        });

        $this->line(sprintf('  %-30s %d ligne(s)', $table, $copiees));
    }

    protected function copierFichiers($source): void
    {
        $chemins = collect()
            ->merge($source->table('documents_numerises')->pluck('chemin_fichier')->filter())
            ->merge($source->table('utilisateurs')->whereNotNull('photo')->pluck('photo'))
            ->unique()
            ->values();

        $copies = 0;
        $manquants = 0;

        foreach ($chemins as $chemin) {
            $cheminLocal = storage_path('app/public/'.ltrim((string) $chemin, '/'));

            if (! is_file($cheminLocal)) {
                $manquants++;
                $this->warn('  Fichier local introuvable : '.$chemin);

                continue;
            }

            Storage::disk('public')->put($chemin, (string) file_get_contents($cheminLocal), 'private');
            $copies++;
        }

        $this->line(sprintf('  Fichiers copiés : %d ; introuvables : %d', $copies, $manquants));
    }

    protected function reinitialiserSequences($cible): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::connection('neon')->hasColumn($table, 'id')) {
                continue;
            }

            $max = $cible->table($table)->max('id');

            if ($max === null) {
                continue;
            }

            $sequence = $cible->selectOne(
                'SELECT pg_get_serial_sequence(?, ?) AS nom',
                ['public.'.$table, 'id']
            )?->nom;

            if (! $sequence) {
                continue;
            }

            $cible->statement('SELECT setval(?, ?, true)', [$sequence, (int) $max]);
        }
    }
}
