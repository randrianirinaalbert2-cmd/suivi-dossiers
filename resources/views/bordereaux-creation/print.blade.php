<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bordereau d'envoi — création — {{ $bordereau->annee }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; margin: 24px auto; max-width: 780px; }
        .centre { text-align: center; }
        .republique { text-align: center; line-height: 1.5; font-size: 14px; margin-bottom: 14px; }
        table.entete { width: 100%; border-collapse: collapse; border: 0; }
        table.entete td { border: 0; padding: 0; vertical-align: top; }
        .gauche { width: 52%; text-align: center; font-size: 12px; line-height: 1.45; }
        .droite { width: 48%; text-align: center; font-size: 12px; line-height: 1.45; }
        .gauche img { width: 86px; height: auto; margin: 4px 0 6px; }
        .tiret { letter-spacing: 1px; line-height: 1.1; }
        .numero { text-align: left; margin-top: 14px; font-size: 12px; }
        .numero .blanc { display: inline-block; width: 46px; }
        .date { text-align: center; margin: 0 0 40px; padding-top: 52px; }
        .titre { text-align: center; font-size: 15px; margin: 22px 0 8px; }
        table.liste { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.liste th { border: 1px solid #000; padding: 2px 4px; font-weight: normal; text-transform: uppercase; font-size: 12px; }
        table.liste td { border-left: 1px solid #000; border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 0 6px; vertical-align: top; }
        table.liste td.obs { text-align: center; vertical-align: middle; }
        .l { height: 21px; line-height: 21px; white-space: nowrap; overflow: hidden; }
        .corps tr.liste-agents td { height: 300px; border-bottom: 0; }
        /* La case « Observations » couvre les deux lignes : elle garde sa
           bordure du bas pour fermer le cadre du tableau. */
        .corps tr.liste-agents td.obs { border-bottom: 1px solid #000; }
        .corps tr.ligne-chef td { height: 130px; }
        .chef { padding-top: 78px; margin-top: 14px; }
        .alerte { margin: 10px 0; padding: 8px; border: 1px solid #b45309; background: #fffbeb; color: #92400e; font-family: Arial, sans-serif; font-size: 12px; text-align: center; }
        .pied { margin-top: 26px; text-align: center; font-style: italic; font-size: 10.5px; line-height: 1.35; }
        .actions { margin-top: 24px; text-align: center; }
        /* Marges reportées sur le body (padding) pour que le navigateur
           n'imprime ni l'adresse de la page, ni la date, ni le numéro de page
           dans les marges de la feuille. */
        @media print { body { margin: 0; max-width: none; padding: 14mm 14mm 12mm 14mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    @php($chef = $bordereau->division?->chefDivision)

    @unless ($chef)
        <div class="alerte no-print">
            Aucun chef de division n'est défini pour cette division : son nom ne pourra pas être imprimé en bas du tableau.
            Ajoutez-le dans Paramétrage → Utilisateurs (rôle « Chef de division »).
        </div>
    @endunless

    <div class="republique">
        REPOBLIKAN'I MADAGASIKARA<br>
        Fitiavana-Tanindrazana-Fandrosoana
    </div>

    <table class="entete">
        <tr>
            <td class="gauche">
                <img src="{{ asset('logo-mef.png') }}" alt="Ministère de l'Économie et des Finances"><br>
                MINISTERE DE L'ECONOMIE ET DES FINANCES
                <div class="tiret">---------------</div>
                SECRETARIAT GENERAL
                <div class="tiret">---------------</div>
                DIRECTION GENERALE DU BUDGET<br>ET DES FINANCES
                <div class="tiret">---------------</div>
                DIRECTION DE LA SOLDE ET DES PENSIONS
                <div class="tiret">----------------</div>
                SERVICE REGIONAL DE LA SOLDE<br>ET DES PENSIONS
                <br><br>
                AMORON'I MANIA
            </td>
            <td class="droite">
                <div class="date">Ambositra le,</div>
                LE CHEF DE SERVICE REGIONAL<br>
                DE LA SOLDE ET DES PENSIONS P.O.<br>
                -<u>AMORON'I MANIA</u>-<br>
                À<br>
                {{ mb_strtoupper($bordereau->destination?->nom ?? '') }}
            </td>
        </tr>
    </table>

    {{-- Le numéro de référence est écrit à la main par le chef de division --}}
    <div class="numero">
        N°<span class="blanc"></span>/{{ $bordereau->annee }}/MEF/SG/DGBF/DSP/SRSP.AMM.SOLDE
    </div>

    <div class="titre">-<strong><u>BORDEREAU D'ENVOI</u></strong>-</div>

    <table class="liste">
        <thead>
            <tr>
                <th style="width: 38px;">N°</th>
                <th>Nom et prénoms</th>
                <th style="width: 92px;">IM</th>
                <th style="width: 70px;">CMVT</th>
                <th style="width: 190px;">Observations</th>
            </tr>
        </thead>
        <tbody class="corps">
            <tr class="liste-agents">
                <td>
                    <div class="l"></div>
                    <div class="l"></div>
                    @foreach ($bordereau->lignes as $index => $ligne)
                        <div class="l" style="text-align: center;">{{ $index + 1 }}</div>
                    @endforeach
                </td>
                <td>
                    <div class="l"><u>Chap : {{ $bordereau->chapitre }} Sigle : {{ $bordereau->lieu?->sigle }} Pq : {{ $bordereau->paquet }}</u></div>
                    <div class="l"></div>
                    @foreach ($bordereau->lignes as $ligne)
                        <div class="l">{{ mb_strtoupper(trim(($ligne->nomAgent() ?? '').' '.($ligne->prenomsAgent() ?? ''))) }}</div>
                    @endforeach
                </td>
                <td>
                    <div class="l"></div>
                    <div class="l"></div>
                    @foreach ($bordereau->lignes as $ligne)
                        <div class="l" style="text-align: center;">{{ $ligne->imAgent() }}</div>
                    @endforeach
                </td>
                <td>
                    <div class="l"></div>
                    <div class="l"></div>
                    @foreach ($bordereau->lignes as $ligne)
                        <div class="l" style="text-align: center;">{{ $ligne->typeDossier?->code }}</div>
                    @endforeach
                </td>
                <td class="obs" rowspan="2">{{ $bordereau->observation }}</td>
            </tr>
            <tr class="ligne-chef">
                <td></td>
                <td><div class="chef">{{ $chef?->nom }}</div></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="pied">
        Direction de la Solde et des Pensions<br>
        Service Régional de la Solde et des Pensions Amoron'i Mania<br>
        Lot 03A 83B Ambohibary Ambositra<br>
        Contacts : 032 11 09 168
    </div>

    <div class="actions no-print">
        <button onclick="window.print()" style="padding: 8px 18px; cursor: pointer;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px;vertical-align:-3px;margin-right:6px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>Imprimer</button>
    </div>
</body>
</html>
