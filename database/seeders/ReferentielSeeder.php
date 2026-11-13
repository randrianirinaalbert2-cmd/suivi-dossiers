<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Division;
use App\Models\Lieu;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\TypePiece;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        // Divisions
        $solde = Division::firstOrCreate(['nom' => 'Solde']);
        $visa = Division::firstOrCreate(['nom' => 'Visa']);

        // Lieux : les codes distinguent les localités ; le sigle porté sur
        // les bordereaux est toujours celui de la direction régionale (236).
        $lieux = [
            ['306', 'Ambositra', '236'],
            ['304', 'Ambatofinandrahana', '236'],
            ['323', 'Manandrina', '236'],
            ['308', 'Fandriana', '236'],
            ['999', 'Autres Ministères', '236'],
        ];

        foreach ($lieux as [$code, $nom, $sigle]) {
            Lieu::updateOrCreate(['code' => $code], ['nom' => $nom, 'sigle' => $sigle]);
        }

        // Destinations : les bordereaux d'envoi réels visent l'un ou l'autre destinataire.
        Destination::firstOrCreate([
            'nom' => 'Monsieur le Directeur de la Solde et des Pensions - Antananarivo',
        ]);
        Destination::firstOrCreate([
            'nom' => 'Monsieur le Directeur de la Solde et des Pensions - Analamanga (SONACO)',
        ]);

        // Services d'origine (exemples tirés des bordereaux reçus)
        ServiceOrigine::firstOrCreate(['nom' => 'Direction Régionale de la Santé Publique Amoron\'i Mania']);
        ServiceOrigine::firstOrCreate(['nom' => 'Centre Hospitalier de Référence du District Niveau II de Fandriana']);

        // Types de dossiers de la division Solde = liste des codes mouvements.
        // Familles : I création, II modification de situation, III positions diverses,
        // IV sanctions, V annulation, VI régularisation, VII exclusion.
        // Les 17 codes de l'affiche du service gardent leurs pièces exigées ;
        // les autres codes de mouvement sont disponibles, pièces à définir par le service.
        $catalogue = [
            // I — Création
            ['01', 'Nomination HEE non fonctionnaire permanent', 'creation', []],
            ['02', 'Nomination d\'un fonctionnaire', 'creation', [
                ['Acte formaté', 2],
                ['Arrêté de nomination', 3],
                ['Attestation de prise de service', 3],
            ]],
            ['03', 'Recrutement d\'un EFA', 'creation', [
                ['Contrat de travail', 3],
                ['Attestation de prise de service', 3],
                ['Certificat administratif', 2],
            ]],
            ['04', 'Recrutement d\'un ELD', 'creation', [
                ['Décision d\'engagement', 2],
                ['Attestation de prise de service', 3],
                ['Certificat administratif', 2],
            ]],
            ['05', 'Nomination HEE fonctionnaire permanent', 'creation', [
                ['Acte formaté', 2],
                ['Décret ou arrêté de nomination', 3],
                ['Attestation de prise de service', 3],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['06', 'Nomination HEE fonctionnaire non permanent', 'creation', []],
            ['07', 'Nomination HEE non fonctionnaire non permanent', 'creation', []],
            ['08', 'Rapatriement du personnel extérieur', 'creation', []],

            // II — Modification de situation
            ['10', 'Intégration', 'modification_situation', [
                ['Acte formaté', 2],
                ['Arrêté d\'intégration', 3],
                ['Certificat administratif pour mandatement', 1],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['11', 'Titularisation', 'modification_situation', [
                ['Acte formaté', 2],
                ['Arrêté de titularisation', 3],
                ['Certificat administratif pour mandatement', 1],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['12', 'Avenant', 'modification_situation', [
                ['Acte formaté', 2],
                ['Avenant', 3],
                ['Certificat administratif pour mandatement', 1],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['13', 'Avancement de classe / échelon', 'modification_situation', [
                ['Acte formaté', 2],
                ['Arrêté d\'avancement', 3],
                ['Certificat administratif pour mandatement', 1],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['14', 'Reclassement d\'un fonctionnaire', 'modification_situation', []],
            ['15', 'Reclassement d\'un EFA ou d\'un ELD', 'modification_situation', []],
            ['16', 'Majoration de salaire d\'ELD', 'modification_situation', []],
            ['17', 'Renouvellement de contrat ou de décision d\'engagement', 'modification_situation', []],
            // Code proposé (acte administratif soumis à visa) — à confirmer par le service.
            ['18', 'Majoration d\'indice (Catégorie VIII)', 'modification_situation', []],
            ['19', 'Indemnité compensatrice de congé non pris', 'modification_situation', [
                ['Acte formaté', 2],
                ['Décision', 3],
                ['Arrêté d\'admission à la retraite', 3],
                ['Certificat administratif pour mandatement', 1],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],

            // III — Positions diverses
            ['20', 'Affectation d\'un fonctionnaire', 'positions_diverses', [
                ['Acte formaté', 2],
                ['Décision d\'affectation', 3],
                ['Attestation de prise de service', 3],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['21', 'En détachement', 'positions_diverses', []],
            ['22', 'En disponibilité', 'positions_diverses', []],
            ['23', 'Position sous les drapeaux (service militaire)', 'positions_diverses', []],
            ['24', 'Mis à la disposition de la FOP', 'positions_diverses', []],
            ['25', 'Régularisation d\'une situation non statuée après 6 mois', 'positions_diverses', []],
            ['26', 'Réintégration après service militaire', 'positions_diverses', []],
            ['27', 'Réintégration après disponibilité', 'positions_diverses', []],
            ['28', 'Réintégration après suspension de solde', 'positions_diverses', [
                ['Acte formaté', 2],
                ['Décision de réintégration', 3],
                ['Attestation de prise de service', 3],
                ['Bulletin de solde ou avis de crédit', 1],
                ['Certificat administratif pour mandatement', 1],
            ]],
            ['29', 'Maintenue en activité', 'positions_diverses', [
                ['Acte formaté', 2],
                ['Décision de maintien', 3],
                ['Bulletin de solde ou avis de crédit', 1],
                ['Certificat administratif pour mandatement', 1],
            ]],

            // IV — Sanctions
            ['30', 'Suspension de solde', 'sanctions', [
                ['Acte formaté', 2],
                ['Décision / arrêté / lettre', 3],
                ['Bulletin de solde ou avis de crédit', 1],
                ['Certificat administratif pour mandatement', 1],
            ]],
            ['31', 'Suspension de fonction', 'sanctions', []],
            ['32', 'Révocation avec droits à pension', 'sanctions', []],
            ['33', 'Révocation sans droits à pension', 'sanctions', []],
            ['34', 'Résiliation de contrat ou de décision d\'engagement', 'sanctions', []],
            ['35', 'Réduction d\'ancienneté', 'sanctions', []],
            ['36', 'Abaissement d\'échelon', 'sanctions', []],
            ['37', 'Rétrogradation', 'sanctions', []],
            ['38', 'Redoublement de stage', 'sanctions', []],
            ['39', 'Redoublement d\'échelon ou de classe', 'sanctions', []],

            // V — Annulation
            // Décès : chaque pièce est exigée en 3 exemplaires.
            ['40', 'Annulation (décès)', 'annulation', [
                ['Acte de décès', 3],
                ['Acte de mariage ou de naissance (enfant)', 3],
                ['CIN légalisée', 3],
                ['Bulletin de solde ou avis de crédit', 3],
            ]],
            ['41', 'Démission', 'annulation', []],
            ['42', 'Annulation (retraite pour limite d\'âge)', 'annulation', [
                ['Décret / arrêté / note / CIN', 1],
                ['Demande', 1],
                ['Bulletin de solde ou avis de crédit', 1],
            ]],
            ['43', 'En retraite proportionnelle', 'annulation', []],
            ['44', 'En retraite d\'office', 'annulation', []],
            ['45', 'En position hors cadre', 'annulation', []],
            ['46', 'Abrogation des HEE pour non fonctionnaire', 'annulation', []],
            ['47', 'Opposition de crédit', 'annulation', []],
            ['48', 'Annulation (code rubrique)', 'annulation', [
                ['Décret / arrêté / décision / lettre', 3],
                ['Acte formaté', 2],
                ['Bulletin de solde ou avis de crédit', 1],
                ['Certificat administratif pour mandatement', 1],
            ]],
            ['49', 'Abrogation HEE fonctionnaire', 'annulation', [
                ['Décret / arrêté d\'abrogation', 3],
                ['Acte formaté', 2],
                ['Bulletin de solde ou avis de crédit', 1],
                ['Certificat administratif pour mandatement', 1],
            ]],

            // VI — Régularisation / modification de situation
            ['60', 'Allocations familiales', 'regularisation', []],
            ['61', 'Diverses indemnités (annuelles, non permanentes)', 'regularisation', []],
            ['62', 'Diverses retenues (non permanentes)', 'regularisation', []],
            ['63', 'Avance sur solde et rappel de solde', 'regularisation', []],
            ['64', 'Rappel différentiel trop perçu (indice)', 'regularisation', []],
            ['65', 'Opposition pension alimentaire', 'regularisation', []],
            ['66', 'Reversement de trop perçu (solde indûment perçue)', 'regularisation', []],
            ['67', 'Régularisation d\'indemnités moins perçues (retenue à rembourser)', 'regularisation', []],
            // Code proposé (acte administratif soumis à visa) — à confirmer par le service.
            ['68', 'Bonification (RSM, DELF – DALF)', 'regularisation', []],
            ['69', 'Rappel différentiel moins perçu (indice)', 'regularisation', []],
            ['70', 'Changement de mode de paiement', 'regularisation', []],
            ['71', 'Création de code rubrique (permanentes, jamais perçues)', 'regularisation', []],
            ['72', 'Modification de code rubrique (permanentes)', 'regularisation', []],
            ['73', 'Attribution du numéro matricule d\'un élève fonctionnaire', 'regularisation', []],
            ['74', 'Affectation à l\'extérieur', 'regularisation', []],
            ['75', 'Diverses modifications autres que le grade (matricule, nom, etc.)', 'regularisation', []],
            ['76', 'Déclassement', 'regularisation', []],
            ['77', 'Validation de service (code rubrique 779)', 'regularisation', []],
            // Code proposé (acte administratif soumis à visa) — à confirmer par le service.
            ['78', 'Indemnité d\'installation de retraite', 'regularisation', []],

            // VII — Exclusion
            ['80', 'Exclusion temporaire', 'exclusion', []],
            ['81', 'Perte de la nationalité malagasy', 'exclusion', []],
            ['82', 'Incarcération', 'exclusion', []],
            ['83', 'Inaptitude définitive', 'exclusion', []],
            ['84', 'Radiation', 'exclusion', []],
            ['85', 'Double emploi', 'exclusion', []],
        ];

        foreach ($catalogue as [$code, $libelle, $famille, $pieces]) {
            $type = TypeDossier::firstOrCreate(
                ['division_id' => $solde->id, 'code' => $code],
                ['libelle' => $libelle]
            );

            // La famille et le libellé d'un code déjà saisi par le service ne
            // sont pas écrasés : on ne complète que la famille manquante.
            if ($type->famille === null) {
                $type->famille = $famille;
                $type->save();
            }

            if ($pieces === []) {
                continue;
            }

            $liaisons = [];
            foreach ($pieces as [$libellePiece, $nombre]) {
                $piece = TypePiece::firstOrCreate(['libelle' => $libellePiece]);
                $liaisons[$piece->id] = ['nb_exemplaires' => $nombre];
            }

            // syncWithoutDetaching préserve les pièces ajoutées par le service ;
            // on met ensuite à jour le nombre d'exemplaires exigé.
            $type->typesPieces()->syncWithoutDetaching($liaisons);
            foreach ($liaisons as $pieceId => $pivot) {
                $type->typesPieces()->updateExistingPivot($pieceId, $pivot);
            }
        }

        // Division Visa : un type provisoire, en attendant les informations du service.
        TypeDossier::firstOrCreate(
            ['division_id' => $visa->id, 'code' => 'V01'],
            ['libelle' => 'Type de dossier Visa (à définir)']
        );

        // Comptes de démonstration (mots de passe à changer avant tout usage réel)
        $utilisateurs = [
            ['Administrateur', 'admin@suivi.test', User::ROLE_ADMINISTRATEUR, null],
            ['RAZAFINDRASOA Fanjatina Bakoly', 'chef.solde@suivi.test', User::ROLE_CHEF_DIVISION, $solde->id],
            ['Responsable Solde', 'responsable.solde@suivi.test', User::ROLE_RESPONSABLE, $solde->id],
        ];

        foreach ($utilisateurs as [$nom, $courriel, $role, $divisionId]) {
            if (User::where('courriel', $courriel)->exists()) {
                continue;
            }

            $utilisateur = new User;
            $utilisateur->forceFill([
                'nom' => $nom,
                'courriel' => $courriel,
                'mot_de_passe' => 'ChangerMoi2026',
                'role' => $role,
                'division_id' => $divisionId,
                'courriel_verifie_le' => now(),
            ])->save();
        }

        // L'application est intranet : aucun courriel n'est réellement envoyé.
        User::whereNull('courriel_verifie_le')->update(['courriel_verifie_le' => now()]);
    }
}
