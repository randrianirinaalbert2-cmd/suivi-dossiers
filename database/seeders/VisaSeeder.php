<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Types de dossiers de la division Visa et pièces à fournir pour viser les
 * actes administratifs, d'après la « Liste des dossiers à fournir pour viser
 * les actes administratifs » (nomenclature du service).
 *
 * Pour chaque type qui a une liste de pièces, les pièces requises sont
 * REMPLACÉES par celles de la nomenclature : une pièce qui n'y figure plus est
 * retirée du type. Les types sans pièces listées (V15 à V18) ne sont pas
 * touchés, ni les dossiers existants.
 *
 * Lancer avec : php artisan db:seed --class=VisaSeeder
 */
class VisaSeeder extends Seeder
{
    public function run(): void
    {
        $divisionId = DB::table('divisions')->where('nom', 'Visa')->value('id');
        if (! $divisionId) {
            $divisionId = DB::table('divisions')->insertGetId([
                'nom' => 'Visa',
                'cree_le' => now(),
                'modifie_le' => now(),
            ]);
        }

        $demande = 'Demande (avec avis favorable du chef hiérarchique)';
        $contrats = 'Photocopie certifiée des contrats et/ou avenants précédents';
        $nonInterruption = 'Attestation de non interruption de service';
        $certificat = 'Certificat administratif';
        $certificatNaissance = 'Certificat administratif avec date de naissance';
        $derniereSituation = 'Photocopie certifiée de la dernière situation';
        $etatService = 'État de service (s\'il y a bonification d\'ancienneté)';
        $nonApplication = 'Attestation de non application de la bonification d\'ancienneté (s\'il existe)';
        $arreteBonification = 'Arrêté de bonification (s\'il existe)';
        $nonAttribution = 'Attestation de non attribution de la bonification d\'ancienneté';
        $cin = 'CIN légalisée';
        $retraite = 'Arrêté d\'admission à la retraite';
        $compteRendu = 'Compte rendu de prise de service';
        $etatConge = 'État de congé';
        $nonJouissance = 'Attestation de non jouissance de congé';
        $equivalence = 'Arrêté d\'équivalence délivré par le FOP';

        $catalogue = [
            ['V01', 'Renouvellement de contrat (agent EFA)', null, [
                [$demande, 2], ['Projet (contrat ou avenant)', 12], [$contrats, 3], ['Fiche n°2', 2], ['Note de présentation', 2], [$nonInterruption, 2], [$certificat, 2], ['Fiche médicale 57/58', 1], ['BIN', 2],
            ]],
            ['V02', 'Avenant (agent EFA)', null, [
                [$demande, 2], ['Projet (contrat ou avenant)', 12], [$contrats, 3], [$nonInterruption, 2], ['Attestation de non jouissance de congé (1er avenant)', 3], ['BIN', 2],
            ]],
            ['V03', 'Intégration', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], ['Fiche d\'intégration', 3], ['Copie d\'acte de naissance original ou CIN légalisée', 3], ['Diplôme certifié conforme à la dernière situation', 3], ['Arrêté d\'équivalence délivré par le FOP (-1an)', 3], [$contrats, 3], [$compteRendu, 2], [$nonInterruption, 2], [$certificat, 2],
            ]],
            ['V04', 'Majoration de salaire (agent ELD)', null, [
                [$demande, 2], ['Projet de décision', 12], ['Photocopie certifiée de la dernière décision', 3], ['Attestation de prise de service pour la 1ère majoration', 2], [$certificat, 2], [$nonInterruption, 2], ['Fiche de proposition d\'augmentation de salaire', 3],
            ]],
            ['V05', 'Avancement de classe', null, [
                [$demande, 2], ['Projet de décision', 6], ['Projet d\'arrêté', 12], ['P.V.', 1], [$derniereSituation, 3], ['BIN', 2], [$nonInterruption, 2], [$etatService, 3], [$nonApplication, 2], [$arreteBonification, 3],
            ]],
            ['V06', 'Avancement d\'échelon', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], [$derniereSituation, 3], [$nonInterruption, 2], [$etatService, 3], [$nonApplication, 2], [$arreteBonification, 3],
            ]],
            ['V07', 'Reclassement indiciaire', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], [$derniereSituation, 2], [$nonInterruption, 2], ['Certificat administratif avec date de naissance ou CIN légalisée', 3], [$etatService, 3], [$nonApplication, 2], [$arreteBonification, 3],
            ]],
            ['V08', 'Majoration d\'indice (catégorie VIII)', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], [$derniereSituation, 2], [$nonInterruption, 2], [$certificatNaissance, 2], [$cin, 3], [$nonApplication, 2], [$arreteBonification, 3],
            ]],
            ['V09', 'Bonification : RSM', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], ['État signalétique certifié par l\'autorité compétente', 3], [$derniereSituation, 2], [$nonInterruption, 2], [$nonAttribution, 2], ['Déclaration sur l\'honneur', 2], ['État de service', 3],
            ]],
            ['V10', 'Bonification : DELF-DALF', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], [$derniereSituation, 2], ['Diplôme DELF et/ou DALF', 3], [$equivalence, 3], [$nonInterruption, 2], [$nonAttribution, 2],
            ]],
            ['V11', 'Titularisation', null, [
                [$demande, 2], ['Projet d\'arrêté', 12], ['P.V.', 1], ['Photocopie certifiée de la dernière situation (arrêté d\'intégration ou arrêté de nomination)', 3], ['État de congé ou attestation de non jouissance de congé', 2], ['Compte rendu de prise de service ou attestation de non interruption de service', 2], ['BIN', 2],
            ]],
            ['V12', 'Indemnité d\'installation de retraite (IIR)', null, [
                [$demande, 2], ['Projet de décision', 12], [$retraite, 3], [$derniereSituation, 3], ['Relevé de service', 3], ['Certificat de non interruption de service', 2], [$cin, 3], ['Pièces de validation (fonctionnaires ayant effectué le contractuel EFA)', 3], ['Cessation de service pour la retraite anticipée', 2],
            ]],
            ['V13', 'Indemnité compensatrice de congé non pris (ICCNP)', null, [
                [$demande, 2], ['Projet de décision', 12], [$retraite, 3], [$derniereSituation, 3], ['Décision de congé des trois dernières années', 3], [$etatConge, 2], ['Titre de congé', 2], [$nonJouissance, 2], [$cin, 3],
            ]],
            ['V14', 'Indemnité en cas de décès', null, [
                ['Acte de décès', 3], ['Acte de mariage du défunt ou acte de naissance de l\'enfant', 3], [$cin, 3],
            ]],

            // Types sans pièces requises : la liste officielle n'est pas encore
            // connue. Elle se complète ensuite dans « Pièces requises »
            // (paramétrage). Les libellés contiennent les mots-clés qui
            // déclenchent l'intitulé du « Soit transmis » (config/soit_transmis.php).
            ['V15', 'Affectation (mutation)', null, []],
            ['V16', 'Rectificatif de l\'arrêté d\'intégration', null, []],
            ['V17', 'Annulation du contrat et renouvellement', null, []],
            ['V18', 'Révision de situation administrative (avancement d\'échelon)', null, []],
        ];

        DB::transaction(function () use ($catalogue, $divisionId) {
            foreach ($catalogue as [$code, $libelle, $famille, $pieces]) {
                $typeId = $this->enregistrerType($divisionId, $code, $libelle, $famille);
                $gardees = [];

                foreach ($pieces as [$libellePiece, $nombre]) {
                    $pieceId = $this->enregistrerPiece($libellePiece);
                    $gardees[] = $pieceId;
                    $this->enregistrerLiaison($typeId, $pieceId, $nombre);
                }

                // Remplacement : on retire les pièces qui ne figurent plus sur la
                // nomenclature. Un type sans pièces listées n'est pas modifié.
                if ($pieces !== []) {
                    $retirees = DB::table('pieces_requises')
                        ->where('type_dossier_id', $typeId)
                        ->whereNotIn('type_piece_id', $gardees)
                        ->delete();

                    $this->command?->info("{$code} : ".count($gardees).' pièce(s)'.($retirees > 0 ? ", {$retirees} retirée(s)" : '').'.');
                }
            }
        });
    }

    private function enregistrerType(int $divisionId, string $code, string $libelle, ?string $famille): int
    {
        $maintenant = now();
        $existant = DB::table('types_dossiers')->where('division_id', $divisionId)->where('code', $code)->first();
        if ($existant) {
            // Une famille déjà attribuée par le service n'est pas écrasée.
            $maj = ['libelle' => $libelle, 'modifie_le' => $maintenant];
            if ($famille !== null) {
                $maj['famille'] = $famille;
            }
            DB::table('types_dossiers')->where('id', $existant->id)->update($maj);

            return $existant->id;
        }

        return DB::table('types_dossiers')->insertGetId(['division_id' => $divisionId, 'code' => $code, 'libelle' => $libelle, 'famille' => $famille, 'cree_le' => $maintenant, 'modifie_le' => $maintenant]);
    }

    private function enregistrerPiece(string $libelle): int
    {
        $existant = DB::table('types_pieces')->where('libelle', $libelle)->first();
        if ($existant) {
            return $existant->id;
        }

        return DB::table('types_pieces')->insertGetId(['libelle' => $libelle, 'cree_le' => now(), 'modifie_le' => now()]);
    }

    private function enregistrerLiaison(int $typeId, int $pieceId, int $nombre): void
    {
        $filtre = ['type_dossier_id' => $typeId, 'type_piece_id' => $pieceId];
        if (DB::table('pieces_requises')->where($filtre)->exists()) {
            DB::table('pieces_requises')->where($filtre)->update(['nb_exemplaires' => $nombre, 'modifie_le' => now()]);

            return;
        }
        DB::table('pieces_requises')->insert($filtre + ['nb_exemplaires' => $nombre, 'cree_le' => now(), 'modifie_le' => now()]);
    }
}
