<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $nonApplication = 'Attestation de non application de la bonification d\'ancienneté (s\'il existe)';
        $arreteBonification = 'Arrêté de bonification (s\'il existe)';
        $nonAttribution = 'Attestation de non attribution de la bonification d\'ancienneté';
        $cin = 'CIN certifiée';
        $retraite = 'Arrêté d\'admission à la retraite';
        $compteRendu = 'Compte rendu de prise de service';
        $etatConge = 'État de congé';
        $nonJouissance = 'Attestation de non jouissance de congé';
        $equivalence = 'Arrêté d\'équivalence délivré par le FOP';

        $catalogue = [
            ['V01', 'Renouvellement de contrat (agent EFA)', null, [
                [$demande, 2], ['Projet (contrat ou avenant)', 7], [$contrats, 3], ['Fiche n°2', 2], ['Note de présentation', 2], [$nonInterruption, 2], [$certificat, 2], ['Fiche médicale 57/58', 1],
            ]],
            ['V02', 'Avenant (agent EFA)', null, [
                [$demande, 2], ['Projet (contrat ou avenant)', 7], [$contrats, 3], [$nonInterruption, 2], ['BIN', 2],
            ]],
            ['V03', 'Intégration', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], ['Fiche d\'intégration', 3], ['Copie d\'acte de naissance original', 2], ['Diplôme certifié conforme à la dernière situation', 3], [$equivalence, 3], [$contrats, 3], [$compteRendu, 2], [$nonInterruption, 2], [$certificat, 2],
            ]],
            ['V04', 'Majoration de salaire (agent ELD)', null, [
                [$demande, 2], ['Projet de décision', 7], ['Photocopie certifiée de la dernière décision', 3], ['Attestation de prise de service pour la 1ère majoration', 2], [$certificat, 2], [$nonInterruption, 2], ['Fiche de proposition d\'augmentation de salaire', 2],
            ]],
            ['V05', 'Avancement de classe', null, [
                [$demande, 2], ['Projet de décision ou d\'arrêté', 7], ['P.V.', 1], [$derniereSituation, 3], ['BIN', 2], [$nonInterruption, 2], [$certificat, 2], [$nonApplication, 2], [$arreteBonification, 2],
            ]],
            ['V06', 'Avancement d\'échelon', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], [$derniereSituation, 3], [$nonInterruption, 1], [$certificat, 3], [$nonApplication, 2], [$arreteBonification, 2],
            ]],
            ['V07', 'Reclassement indiciaire', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], [$derniereSituation, 2], [$nonInterruption, 2], [$certificatNaissance, 2], [$cin, 2], [$nonApplication, 2], [$arreteBonification, 2],
            ]],
            ['V08', 'Majoration d\'indice (catégorie VIII)', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], [$derniereSituation, 2], [$nonInterruption, 2], [$certificatNaissance, 2], [$cin, 2], [$nonApplication, 2], [$arreteBonification, 3],
            ]],
            ['V09', 'Bonification : RSM', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], ['État signalétique certifié par l\'autorité compétente', 3], [$derniereSituation, 2], [$nonInterruption, 2], [$nonAttribution, 2], ['Déclaration sur l\'honneur', 2], [$certificat, 3], ['État de service', 1],
            ]],
            ['V10', 'Bonification : DELF-DALF', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], [$derniereSituation, 2], ['Diplôme DELF et/ou DALF', 3], [$equivalence, 3], [$nonInterruption, 2], [$certificat, 2], [$nonAttribution, 2],
            ]],
            ['V11', 'Titularisation', null, [
                [$demande, 2], ['Projet d\'arrêté', 7], ['P.V.', 3], [$derniereSituation, 2], [$etatConge, 2], [$nonJouissance, 2], [$compteRendu, 2], [$nonInterruption, 2], ['BIN', 2],
            ]],
            ['V12', 'Indemnité d\'installation de retraite (IIR)', null, [
                [$demande, 2], ['Projet de décision', 7], [$retraite, 3], [$derniereSituation, 3], ['Relevé de service', 3], ['Certificat de non interruption de service', 2], [$cin, 2], ['Pièces de validation (fonctionnaires ayant effectué le contractuel EFA)', 3], ['Cessation de service pour la retraite anticipée', 2],
            ]],
            ['V13', 'Indemnité compensatrice de congé non pris (ICCNP)', null, [
                [$demande, 2], ['Projet de décision', 7], [$retraite, 3], [$derniereSituation, 3], ['Décision de congé des trois dernières années', 3], [$etatConge, 2], ['Titre de congé', 2], [$nonJouissance, 2], ['CIN légalisée', 3],
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

        foreach ($catalogue as [$code, $libelle, $famille, $pieces]) {
            $typeId = $this->enregistrerType($divisionId, $code, $libelle, $famille);
            foreach ($pieces as [$libellePiece, $nombre]) {
                $pieceId = $this->enregistrerPiece($libellePiece);
                $this->enregistrerLiaison($typeId, $pieceId, $nombre);
            }
        }
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
