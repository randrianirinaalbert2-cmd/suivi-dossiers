<?php

/*
|--------------------------------------------------------------------------
| Note « SOIT TRANSMIS » du Visa
|--------------------------------------------------------------------------
|
| Réglages de la note remise au contrôle financier. Pour changer un
| destinataire (nouvelle personne, nouvelle formule) ou ajouter un intitulé,
| il suffit de modifier ce fichier : aucune vue n'est à toucher.
|
*/

return [

    // Destinataires possibles : chaque note reçoit celui de son type de
    // dossier, et l'utilisateur peut le changer sur la page.
    'destinataires' => [
        'delegue' => [
            'libelle' => 'Délégué Régional du Contrôle Financier — Amoron\'i Mania',
            'lignes' => [
                'Madame LE DELEGUE REGIONAL',
                'DU CONTROLE FINANCIER',
                '- AMORON\'I MANIA -',
            ],
        ],
        'directeur' => [
            'libelle' => 'Directeur Général du Contrôle Financier — Antananarivo',
            'lignes' => [
                'Monsieur LE DIRECTEUR GENERAL DU',
                'CONTROLE FINANCIER',
                '- ANTANANARIVO -',
            ],
        ],
    ],

    'destinataire_par_defaut' => 'delegue',

    /*
    | Intitulés d'après les modèles de « Soit transmis » du Visa.
    |
    | La première règle qui correspond au libellé du type de dossier est
    | retenue (majuscules et minuscules sans importance) :
    |   - mots         : il suffit que l'un de ces mots figure dans le libellé ;
    |   - sauf         : (facultatif) la règle est ignorée si l'un de ces mots figure ;
    |   - intitule     : la phrase imprimée, avant « aux noms de » / « au nom de » ;
    |   - destinataire : (facultatif) « directeur » pour le Directeur Général
    |                    du Contrôle Financier, sinon le Délégué Régional.
    |
    | Les règles les plus précises doivent rester en haut de la liste.
    */
    'intitules' => [
        ['mots' => ['rectificatif'], 'intitule' => 'Projet Rectificatif de l\'arrêté portant Intégration'],
        ['mots' => ['annulation du contrat'], 'intitule' => 'Projet de décision portant Annulation du contrat et Renouvellement de Contrat de Travail et Avenant'],
        ['mots' => ['affectation'], 'intitule' => 'Projet de décision portant Affectation', 'destinataire' => 'directeur'],
        ['mots' => ['avenant'], 'intitule' => 'Projet portant Avenant'],
        ['mots' => ['majoration de salaire'], 'intitule' => 'Projet portant Majoration de salaire'],
        ['mots' => ['renouvellement'], 'intitule' => 'Projet portant Renouvellement de contrat'],
        ['mots' => ['congé'], 'intitule' => 'Projet de décision portant Octroi d\'une Indemnité Compensatrice de Congé non pris'],
        ['mots' => ['installation'], 'intitule' => 'Projet de décision portant Octroi d\'une Indemnité d\'Installation de Retraite', 'destinataire' => 'directeur'],
        ['mots' => ['intégration', 'integration'], 'intitule' => 'Projet d\'arrêté portant Intégration'],
        ['mots' => ['titularisation'], 'intitule' => 'Projet d\'arrêté portant Titularisation'],
        ['mots' => ['majoration d\'indice'], 'intitule' => 'Projet d\'arrêté portant majoration d\'indice'],
        ['mots' => ['reclassement'], 'intitule' => 'Projet d\'arrêté portant RECLASSEMENT INDICIAIRE'],
        ['mots' => ['rsm'], 'intitule' => 'Projet d\'arrêté portant Attribution d\'une bonification RSM', 'destinataire' => 'directeur'],
        ['mots' => ['delf', 'dalf'], 'intitule' => 'Projet d\'arrêté portant Attribution d\'une bonification d\'ancienneté', 'destinataire' => 'directeur'],
        ['mots' => ['bonification'], 'intitule' => 'Projet d\'arrêté portant Attribution d\'une bonification', 'destinataire' => 'directeur'],
        ['mots' => ['révision'], 'intitule' => 'Projet d\'arrêté portant révision de situation administrative et avancement d\'échelon'],
        ['mots' => ['classe'], 'sauf' => ['échelon'], 'intitule' => 'Projet d\'arrêté et de décision portant Avancement de classe'],
        ['mots' => ['échelon'], 'sauf' => ['classe'], 'intitule' => 'Projet d\'arrêté portant Avancement d\'Echelon'],
    ],

    // Sinon : cette formule, suivie du libellé du type de dossier.
    'intitule_par_defaut' => 'Projet d\'arrêté portant',

];
