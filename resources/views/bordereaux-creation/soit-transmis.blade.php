<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soit transmis — @isset($depuisRecu)bordereau reçu n° {{ $depuisRecu->num_arrivee }}@else bordereau de création n° {{ $bordereau->annee }}/{{ $bordereau->num_ordre }}@endisset</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #000; margin: 24px auto; max-width: 780px; }
        table { border-collapse: collapse; }
        table.entete { width: 100%; border: 0; }
        table.entete td { border: 0; padding: 0; vertical-align: top; text-align: center; }
        .gauche { width: 42.5%; font-size: 10.5px; line-height: 1.35; }
        .gauche img { width: 62px; height: auto; margin-bottom: 4px; }
        .centre { width: 30%; font-size: 12.5px; line-height: 1.4; }
        .droite { width: 27.5%; font-size: 12.5px; line-height: 1.5; }
        .titre { font-size: 14px; margin-bottom: 10px; }
        table.grille { width: 100%; table-layout: fixed; margin-top: 26px; }
        table.grille th, table.grille td.case { border: 1px solid #000; text-align: center; vertical-align: middle; height: 30px; padding: 2px 6px; font-weight: normal; }
        table.grille th { height: 34px; font-size: 13px; }
        table.grille td.signatures { position: relative; border: 0; vertical-align: top; padding: 14px 6px 0; }
        table.interne { width: 100%; border: 0; }
        /* Les deux blocs de signature tiennent sur 3 lignes chacun, à la
           taille du modèle (≈ 8,5 pt), sans retour à la ligne parasite. */
        table.interne td { border: 0; vertical-align: top; text-align: center; font-size: 11.5px; line-height: 1.5; width: 47%; padding: 0 3px; }
        table.interne td.sig-droite { width: 53%; }
        /* Nom du signataire : 4e ligne, placée en bas du bloc de signature. */
        .nom { position: absolute; left: 6px; bottom: 113px; width: 47%; text-align: center; }
        .actions { margin-top: 24px; text-align: center; }
        /* Cases à saisir directement sur la page : l'encadré disparaît à l'impression. */
        .saisie { width: 100%; border: 0; border-bottom: 1px dashed #9ca3af; background: transparent; text-align: center; font: inherit; padding: 2px 0; }
        @media print { .saisie { border: 0; } .saisie::placeholder { color: transparent; } }
        /* Marges reportées sur le body (padding) pour que le navigateur
           n'imprime ni l'adresse de la page, ni la date, ni le numéro de page
           dans les marges de la feuille. */
        @media print { body { margin: 0; max-width: none; padding: 12mm 12mm 10mm 12mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $lignes = $bordereau->lignes;
        $nombre = $lignes->count();
        // Le tableau MOUVEMENT / IM compte au moins 9 rangées, comme sur le modèle.
        $rangees = max($nombre, 9);
        // Signataire de la note « SOIT TRANSMIS » : le chef de division de la
        // division concernée (P.O.). Si aucun chef n'est défini, on retombe sur
        // le nom du chef de la Solde.
        $signataire = $bordereau->division?->chefDivision?->nom
            ?? $depuisRecu?->division?->chefDivision?->nom
            ?? 'RAZAFINDRASOA Fanjatina Bakoly';
    @endphp

    <table class="entete">
        <tr>
            <td class="gauche">
                <img src="{{ asset('logo-mef.png') }}" alt="Ministère de l'Économie et des Finances"><br>
                SECRETARIAT GENERAL<br>
                DIRECTION GENERALE DU BUDGET ET DES FINANCES<br>
                DIRECTION DE LA SOLDE ET DES PENSIONS<br>
                SERVICE REGIONAL DE LA SOLDE ET DES PENSIONS<br>
                -AMORON'I MANIA-
            </td>
            <td class="centre">
                REPOBLIKAN'I MADAGASIKARA<br>
                Fitiavana-Tanindrazana-Fandrosoana
            </td>
            <td class="droite">
                <div class="titre">SOIT TRANSMIS</div>
                A<br>
                Monsieur le Chef de Service<br>
                Central de la Solde<br>
                <u>-Antananarivo-</u>
            </td>
        </tr>
    </table>

    <table class="grille">
        <colgroup>
            <col style="width: 16%"><col style="width: 13%"><col style="width: 30%"><col style="width: 23%"><col style="width: 18%">
        </colgroup>
        <thead>
            <tr>
                <th>Sigle de l'agent</th>
                <th>N° paquet</th>
                <th>Nombre acte formaté Mouvement</th>
                <th>MOUVEMENT</th>
                <th>IM</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $rangees; $i++)
                @php($ligne = $lignes[$i] ?? null)
                <tr>
                    @if ($i === 0)
                        <td class="case">{{ $bordereau->lieu?->sigle }}</td>
                        <td class="case">
                            @isset($depuisRecu)
                                <input class="saisie" type="text" maxlength="10" aria-label="N° paquet" value="{{ $bordereau->paquet }}">
                            @else
                                {{ $bordereau->paquet }}
                            @endisset
                        </td>
                        <td class="case" id="nombre-actes">{{ $nombreActes }}</td>
                    @elseif ($i === 1)
                        <td class="signatures" colspan="3" rowspan="{{ $rangees - 1 }}">
                            <table class="interne">
                                <tr>
                                    <td>
                                        Vu et Vérifié<br>
                                        Le Chef de Service Régional de la<br>
                                        Solde et des Pensions P.O.
                                        <div class="nom">{{ $signataire }}</div>
                                    </td>
                                    <td class="sig-droite">
                                        « POUR ATTRIBUTION »<br>
                                        Antananarivo le,<br>
                                        Le Chef de Service Central de la Solde
                                    </td>
                                </tr>
                            </table>
                        </td>
                    @endif
                    <td class="case">{{ $ligne?->typeDossier?->code }}</td>
                    <td class="case">
                        @php($im = $ligne?->agent_im ?? $ligne?->agent?->im)
                        @if (isset($depuisRecu) && $ligne !== null && blank($im))
                            <input class="saisie" type="text" maxlength="20" aria-label="IM à saisir" placeholder="IM">
                        @else
                            {{ $im }}
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="actions no-print">
        {{-- Le formulaire doit recharger la note « Soit transmis » (et non le
             bordereau d'envoi) pour reprendre la valeur saisie. --}}
        @isset($depuisRecu)
            {{-- Pas de rechargement : le paquet et les IM saisis sur la page seraient perdus. --}}
            <form method="GET" onsubmit="return false;">
        @else
            <form method="GET" action="{{ route('bordereaux-creation.soit-transmis', $bordereau) }}">
        @endisset
            <label for="actes" style="font-size: 13px;">
                Nombre d'actes formatés (facultatif)
                <input id="actes" name="actes" type="number" min="0" value="{{ $nombreActes }}"
                       style="width: 80px; margin-left: 6px; padding: 4px 6px; border: 1px solid #9ca3af; border-radius: 4px;">
            </label>
            <button type="button" onclick="window.print()" style="margin-left: 10px; padding: 8px 18px; cursor: pointer;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px;vertical-align:-3px;margin-right:6px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>Imprimer</button>
        </form>
    </div>

    <script>
        (function () {
            const champ = document.getElementById('actes');
            const cellule = document.getElementById('nombre-actes');

            if (champ === null || cellule === null) {
                return;
            }

            // La case se remplit immédiatement, sans recharger la page.
            champ.addEventListener('input', function () {
                cellule.textContent = champ.value;
            });
        })();
    </script>
</body>
</html>
