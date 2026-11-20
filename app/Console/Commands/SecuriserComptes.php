<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * Remplace les mots de passe de test par des mots de passe forts sur Neon.
 *
 * Les mots de passe sont lus dans un fichier local non versionné
 * (deploy/comptes.json) afin de ne jamais apparaître dans le chat ni dans
 * le dépôt GitHub.
 */
class SecuriserComptes extends Command
{
    protected $signature = 'deploy:securiser-comptes
                            {--fichier=deploy/comptes.json : Fichier JSON courriel => mot de passe}';

    protected $description = 'Remplacer les mots de passe de test sur la base Neon';

    public function handle(): int
    {
        $chemin = base_path((string) $this->option('fichier'));

        if (! is_file($chemin)) {
            $this->error('Fichier introuvable : '.$chemin);

            return self::FAILURE;
        }

        $comptes = json_decode((string) file_get_contents($chemin), true);

        if (! is_array($comptes) || $comptes === []) {
            $this->error('Le fichier doit contenir un objet JSON courriel => mot de passe.');

            return self::FAILURE;
        }

        $cible = DB::connection('neon');

        try {
            $cible->getPdo();
        } catch (Throwable $e) {
            $this->error('Base Neon inaccessible : '.$e->getMessage());

            return self::FAILURE;
        }

        foreach ($comptes as $courriel => $motDePasse) {
            $utilisateur = $cible->table('utilisateurs')
                ->where('courriel', $courriel)
                ->first();

            if (! $utilisateur) {
                $this->error('Compte introuvable sur Neon : '.$courriel);

                continue;
            }

            $cible->table('utilisateurs')
                ->where('id', $utilisateur->id)
                ->update([
                    'mot_de_passe' => Hash::make((string) $motDePasse),
                    'modifie_le' => now(),
                ]);

            $this->info('Mot de passe remplacé : '.$courriel);
        }

        $this->warn('Conservez le fichier de mots de passe en lieu sûr, puis supprimez-le.');

        return self::SUCCESS;
    }
}
