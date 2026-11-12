<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bordereau d'envoi (modèle) — arrivée n° {{ $bordereau->num_arrivee }}</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 14mm 12mm 14mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 14px; color: #000; margin: 24px auto; max-width: 780px; }
        .republique { text-align: center; line-height: 1.5; font-size: 14px; margin-bottom: 14px; }
        table.entete { width: 100%; border-collapse: collapse; border: 0; }
        table.entete td { border: 0; padding: 0; vertical-align: top; }
        .gauche { width: 50%; text-align: center; font-size: 13px; line-height: 1.5; padding-right: 14px; }
        .droite { width: 50%; text-align: center; font-size: 13px; line-height: 1.5; }
        .tiret { letter-spacing: 1px; line-height: 1.1; }
        .date { text-align: left; margin: 0 0 18px; padding-left: 20px; }
        .numero { margin-top: 16px; font-size: 13px; }
        .titre { text-align: center; font-size: 17px; letter-spacing: 3px; margin: 22px 0 10px; }
        table.liste { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.liste th { border: 1px solid #000; padding: 6px 4px; font-weight: bold; text-transform: uppercase; font-size: 13px; }
        table.liste td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 0 8px; vertical-align: top; height: 23px; line-height: 23px; font-size: 13.5px; }
        table.liste tr.derniere td { border-bottom: 1px solid #000; }
        table.liste tr.total td { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: bold; }
        table.liste td.nombre { text-align: center; }
        table.liste td.obs { text-align: center; vertical-align: middle; border-bottom: 1px solid #000; }
        table.liste tr.vide td { height: 70px; }
        .groupe { padding-top: 6px; }
        .cachet { margin-top: 34px; color: #000; font-family: 'Times New Roman', Times, serif; font-size: 13.5px; line-height: 1.6; }        .actions { margin-top: 24px; text-align: center; }
        .note { margin-top: 14px; text-align: center; font-family: Arial, sans-serif; font-size: 11px; color: #6b7280; }
        @media print { body { margin: 0; max-width: none; } .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        // Dossiers du bordereau regroupés par type de dossier (une rubrique « Dossier portant … » par type).
        $groupes = $bordereau->dossiers->sortBy('id')->groupBy('type_dossier_id');
        $total = $bordereau->dossiers->count();
        // Nombre de lignes du tableau : la localité (si elle existe), 1 titre + n agents par groupe, puis une ligne vide et la ligne TOTAL.
        $nbLignes = $groupes->count() + $total + 2 + ($bordereau->lieu ? 1 : 0);

        $objet = mb_strtoupper(trim((string) ($bordereau->objet ?? '')));
        $transmisPour = null;
        if (str_contains($objet, 'MANDATEMENT')) {
            $transmisPour = 'TRANSMIS POUR MANDATEMENT';
        } elseif (str_contains($objet, 'VISA')) {
            $transmisPour = 'TRANSMIS POUR VISA';
        }
        $obsTexte = trim((string) ($bordereau->observation ?? ''));
        $obsAffichee = mb_strtoupper(collect([$transmisPour, $obsTexte])->filter()->implode(' — '));
    @endphp

    <div class="republique">
        REPOBLIKAN'I MADAGASIKARA<br>
        Fitiavana-Tanindrazana-Fandrosoana
    </div>

    <table class="entete">
        <tr>
            <td class="gauche">
                {{ mb_strtoupper($bordereau->serviceOrigine?->nom ?? '') }}
                <div class="tiret">---------------</div>
            </td>
            <td class="droite">
                <div class="date">Ambositra, le {{ $bordereau->date_bordereau?->format('d/m/Y') }}</div>
                Monsieur le Chef de Service Régional de la Solde et des Pensions<br>
                Amoron'i Mania
            </td>
        </tr>
    </table>

    <div class="numero">N° {{ $bordereau->reference }}</div>

    <div class="titre">BORDEREAU D'ENVOI</div>

    <table class="liste">
        <thead>
            <tr>
                <th>Désignation des pièces</th>
                <th style="width: 110px;">Nombre</th>
                <th style="width: 150px;">Observations</th>
            </tr>
        </thead>
        <tbody>
            @php($premiere = true)
            @if ($bordereau->lieu)
                <tr>
                    <td class="groupe">LOCALITE : {{ $bordereau->lieu->code }}</td>
                    <td class="nombre"></td>
                    <td class="obs" rowspan="{{ $nbLignes }}">{{ $obsAffichee }}</td>
                    @php($premiere = false)
                </tr>
            @endif
            @foreach ($groupes as $dossiers)
                <tr>
                    <td class="groupe">Dossier portant {{ mb_strtolower($dossiers->first()->typeDossier?->libelle ?? '') }} aux noms de :</td>
                    <td class="nombre"></td>
                    @if ($premiere)
                        <td class="obs" rowspan="{{ $nbLignes }}">{{ $obsAffichee }}</td>
                        @php($premiere = false)
                    @endif
                </tr>
                @foreach ($dossiers as $dossier)
                    <tr>
                        <td>- {{ mb_strtoupper($dossier->agent?->nom ?? '') }} {{ $dossier->agent?->prenoms }} &nbsp; IM : {{ $dossier->agent?->im }}</td>
                        <td class="nombre">01</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="vide">
                <td></td>
                <td></td>
                @if ($premiere)
                    <td class="obs" rowspan="{{ $nbLignes }}">{{ $obsAffichee }}</td>
                @endif
            </tr>
            <tr class="total">
                <td>TOTAL</td>
                <td class="nombre">{{ str_pad((string) $total, 2, '0', STR_PAD_LEFT) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="cachet">
        Arrivée le {{ $bordereau->date_arrivee?->format('d/m/Y') }}<br>
        Sous n° {{ $bordereau->num_arrivee }}<br>
        Service régional de la Solde<br>
        et des Pensions Amoron'i Mania
    </div>

    <div class="note no-print">
        Reproduction du bordereau d'envoi à partir des données enregistrées (modèle de consultation).
    </div>

    <div class="actions no-print">
        <button onclick="window.print()" style="padding: 8px 18px; cursor: pointer;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px;vertical-align:-3px;margin-right:6px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>Imprimer</button>
    </div>
</body>
</html>
