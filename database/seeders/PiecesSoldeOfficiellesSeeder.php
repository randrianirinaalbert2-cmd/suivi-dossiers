<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Pièces officielles à fournir pour le mandatement de solde.
 *
 * Cette liste remplace intégralement les pièces des types de dossiers Solde
 * qu'elle décrit : toute pièce absente de la liste officielle est retirée,
 * les quantités sont réappliquées et l'ordre de saisie suit le document du
 * service. Les autres codes (01, 06 à 09, 18, 21 à 27, 31 à 39, 41, 43 à 47,
 * 61 à 85) ne sont pas touchés : leur liste n'est pas encore connue.
 *
 * Le nombre entre parenthèses est le nombre d'exemplaires exigés. Les pièces
 * conditionnelles (17 et 60) sont décrites dans la constante CONDITIONS.
 */
class PiecesSoldeOfficiellesSeeder extends Seeder
{
    /**
     * @var array<string, list<array{0: string, 1: int}>>
     */
    private const PIECES = [
        // I — Création
        '02' => [
            ['Acte formaté', 2],
            ['Arrêté de nomination', 3],
            ['Attestation de prise de service', 3],
        ],
        '03' => [
            ['Contrat de travail', 3],
            ['Attestation de prise de service', 3],
            ['Certificat administratif', 2],
        ],
        '04' => [
            ['Décision d\'engagement', 2],
            ['Attestation de prise de service', 3],
            ['Certificat administratif', 2],
        ],
        '05' => [
            ['Acte formaté', 2],
            ['Décret ou arrêté de nomination', 3],
            ['Attestation de prise de service', 3],
            ['Bulletin de solde ou avis de crédit', 1],
        ],

        // II — Modification de situation
        // Le nombre de base de l'acte formaté est 2 ; un dossier marqué
        // « avec rappel » en exige 4 (voir Dossier::piecesExigees()).
        '10' => [
            ['Acte formaté', 2],
            ['Arrêté d\'intégration', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '11' => [
            ['Acte formaté', 2],
            ['Arrêté de titularisation', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '12' => [
            ['Acte formaté', 2],
            ['Avenant', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '13' => [
            ['Acte formaté', 2],
            ['Arrêté d\'avancement', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '14' => [
            ['Acte formaté', 2],
            ['Arrêté de reclassement', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '15' => [
            ['Acte formaté', 2],
            ['Avenant', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '16' => [
            ['Acte formaté', 2],
            ['Décision de majoration', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '17' => [
            ['Acte formaté', 2],
            ['Contrat de travail', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
            // Exigée seulement si le contrat a été interrompu plus de 3 mois.
            ['Attestation de non interruption de service', 1],
        ],
        '19' => [
            ['Acte formaté', 2],
            ['Décision', 3],
            ['Arrêté d\'admission à la retraite', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],

        // III — Positions diverses
        '20' => [
            ['Acte formaté', 2],
            ['Décision d\'affectation', 3],
            ['Attestation de prise de service', 3],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '28' => [
            ['Acte formaté', 2],
            ['Décision de réintégration', 3],
            ['Attestation de prise de service', 3],
            ['Bulletin de solde ou avis de crédit', 1],
            ['Certificat administratif pour mandatement', 1],
        ],
        '29' => [
            ['Acte formaté', 2],
            ['Décision de maintien', 3],
            ['Bulletin de solde ou avis de crédit', 1],
            ['Certificat administratif pour mandatement', 1],
        ],

        // IV — Sanctions
        '30' => [
            ['Acte formaté', 2],
            ['Décision / arrêté / lettre', 3],
            ['Bulletin de solde ou avis de crédit', 1],
            ['Certificat administratif pour mandatement', 1],
        ],

        // V — Annulation
        '40' => [
            ['Acte de décès', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '42' => [
            ['Décret / arrêté / note / CIN', 1],
            ['Demande', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],
        '48' => [
            ['Décret / arrêté / décision / lettre', 3],
            ['Acte formaté', 2],
            ['Bulletin de solde ou avis de crédit', 1],
            ['Certificat administratif pour mandatement', 1],
        ],
        '49' => [
            ['Décret / arrêté d\'abrogation', 3],
            ['Acte formaté', 2],
            ['Bulletin de solde ou avis de crédit', 1],
            ['Certificat administratif pour mandatement', 1],
        ],

        // VI — Régularisation
        '50' => [
            ['Acte formaté', 2],
            ['Décision d\'installation', 3],
            ['Relevé de service', 3],
            ['Arrêté d\'admission à la retraite', 3],
            ['Certificat administratif pour mandatement', 1],
            ['Bulletin de solde ou avis de crédit', 1],
        ],

        // VII — Allocations familiales
        // Liste fournie par le service : les pièces liées aux enfants et à la
        // situation familiale sont conditionnelles (voir CONDITIONS).
        '60' => [
            ['Acte formaté', 2],
            ['Copie de tous les enfants', 3],
            ['Acte de mariage', 3],
            ['Certificat de célibat', 3],
            ['Avis de crédit', 1],
            ['Attestation de non paiement', 3],
            ['Charge des familles (CF)', 3],
            ['Certificat administratif', 1],
        ],
    ];

    /**
     * Conditions attachées aux pièces de certains types.
     *
     * - condition : la pièce n'est exigée que si la situation est vraie ;
     * - condition_exemplaires / nb_exemplaires_condition : le nombre
     *   d'exemplaires est remplacé lorsque la condition est vraie ;
     * - note : précision affichée au guichet.
     *
     * @var array<string, array<string, array{condition?: string, condition_exemplaires?: string, nb_exemplaires_condition?: int, note?: string}>>
     */
    private const CONDITIONS = [
        '17' => [
            'Attestation de non interruption de service' => [
                'condition' => 'interruption_contrat_plus_3_mois',
            ],
        ],
        '60' => [
            'Acte formaté' => [
                'condition_exemplaires' => 'enfant_moins_de_4_ans',
                'nb_exemplaires_condition' => 4,
            ],
            'Copie de tous les enfants' => [
                'condition' => 'a_des_enfants',
            ],
            'Acte de mariage' => [
                'condition' => 'marie',
            ],
            'Certificat de célibat' => [
                'condition' => 'celibataire',
            ],
            'Avis de crédit' => [
                'note' => 'Quantité selon les pièces déposées par l\'agent',
            ],
            'Attestation de non paiement' => [
                'condition' => 'enfant_non_integre',
                'note' => '2 originaux + 1 photocopie',
            ],
            'Charge des familles (CF)' => [],
        ],
    ];

    public function run(): void
    {
        $divisionId = DB::table('divisions')->where('nom', 'Solde')->value('id');

        if ($divisionId === null) {
            return;
        }

        DB::transaction(function () use ($divisionId) {
            foreach (self::PIECES as $code => $pieces) {
                $typeDossierId = DB::table('types_dossiers')
                    ->where('division_id', $divisionId)
                    ->where('code', $code)
                    ->value('id');

                if ($typeDossierId === null) {
                    continue;
                }

                // Remplacement complet : la liste officielle fait foi.
                DB::table('pieces_requises')->where('type_dossier_id', $typeDossierId)->delete();

                $maintenant = now();
                foreach ($pieces as [$libelle, $nombre]) {
                    $conditions = self::CONDITIONS[$code][$libelle] ?? [];

                    DB::table('pieces_requises')->insert([
                        'type_dossier_id' => $typeDossierId,
                        'type_piece_id' => $this->pieceId($libelle),
                        'nb_exemplaires' => $nombre,
                        'condition' => $conditions['condition'] ?? null,
                        'nb_exemplaires_condition' => $conditions['nb_exemplaires_condition'] ?? null,
                        'condition_exemplaires' => $conditions['condition_exemplaires'] ?? null,
                        'note' => $conditions['note'] ?? null,
                        'cree_le' => $maintenant,
                        'modifie_le' => $maintenant,
                    ]);
                }
            }
        });
    }

    /**
     * Retourne l'identifiant de la pièce, en la créant si elle n'existe pas.
     */
    private function pieceId(string $libelle): int
    {
        $existant = DB::table('types_pieces')->where('libelle', $libelle)->value('id');

        if ($existant !== null) {
            return (int) $existant;
        }

        return (int) DB::table('types_pieces')->insertGetId([
            'libelle' => $libelle,
            'cree_le' => now(),
            'modifie_le' => now(),
        ]);
    }
}
