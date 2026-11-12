<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soit transmis (Visa) — arrivée n° {{ $bordereau->num_arrivee }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 14px; color: #000; margin: 24px auto; max-width: 780px; }
        table { border-collapse: collapse; }
        .barre { margin-bottom: 18px; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; font-family: Arial, Helvetica, sans-serif; font-size: 13px; text-align: center; }
        .barre select { margin: 0 8px; padding: 5px 8px; border: 1px solid #9ca3af; border-radius: 4px; }
        .barre button { padding: 6px 16px; cursor: pointer; }
        .note { padding-bottom: 36px; margin-bottom: 36px; border-bottom: 1px dashed #9ca3af; }
        .note:last-of-type { border-bottom: 0; }
        .republique { text-align: center; line-height: 1.4; font-size: 14px; margin-bottom: 12px; }
        table.entete { width: 100%; border: 0; }
        table.entete td { border: 0; padding: 0; vertical-align: top; text-align: center; }
        .gauche { width: 50%; font-size: 12px; line-height: 1.4; }
        .gauche img { width: 62px; height: auto; margin-bottom: 4px; }
        .droite { width: 50%; font-size: 14px; line-height: 1.5; }
        .titre { margin: 14px 0 22px; }
        .numero { margin-top: 16px; font-size: 12px; }
        .objet { margin: 12px 0 8px; }
        ol.agents, ul.agents { margin: 0 0 14px; padding-left: 38px; line-height: 1.75; }
        ul.agents { list-style: none; }
        .retour { margin: 14px 0 6px; text-align: justify; }
        .signature { margin-left: 52%; text-align: center; line-height: 1.5; }
        @media print {
            body { margin: 0; max-width: none; padding: 12mm 14mm 10mm 14mm; }
            .no-print { display: none; }
            .note { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; page-break-after: always; }
            .note:last-of-type { page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="barre no-print">
        <label for="destinataire">Destinataire :</label>
        <select id="destinataire">
            <option value="" selected>Selon le type de dossier (par défaut)</option>
            @foreach ($destinataires as $cle => $destinataire)
                <option value="{{ $cle }}">{{ $destinataire['libelle'] }}</option>
            @endforeach
        </select>
        <button type="button" onclick="window.print()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px;vertical-align:-3px;margin-right:6px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>Imprimer</button>
        <div style="margin-top: 6px; color: #6b7280; font-size: 12px;">
            {{ count($notes) }} note(s), une par type de dossier. Le n° et la date se complètent à la main. Le destinataire suit le type de dossier ; la liste permet de l'imposer à toutes les notes.
        </div>
    </div>

    @foreach ($notes as $note)
        <section class="note">
            <div class="republique">
                REPOBLIKAN'I MADAGASIKARA<br>
                Fitiavana- Tanindrazana- Fandrosoana<br>
                --------oOo-------
            </div>

            <table class="entete">
                <tr>
                    <td class="gauche">
                        <img src="{{ asset('logo-mef.png') }}" alt="Ministère de l'Économie et des Finances"><br>
                        ---------------------------<br>
                        SECRETARIAT GENERAL<br>
                        ---------------------------<br>
                        DIRECTION GENERALE DU BUDGET<br>
                        ET DES FINANCES<br>
                        ---------------------------<br>
                        DIRECTION DE LA SOLDE ET DES PENSIONS<br>
                        ---------------------------<br>
                        SERVICE REGIONAL DE LA SOLDE<br>
                        ET DES PENSIONS<br>
                        -AMORON'I MANIA-
                    </td>
                    <td class="droite">
                        <div class="titre">SOIT TRANSMIS</div>
                        A
                        <div class="destinataire" data-defaut="{{ $note['destinataire'] }}" style="margin-top: 14px;">
                            @foreach ($destinataires[$note['destinataire']]['lignes'] as $ligne)
                                {{ $ligne }}@if (! $loop->last)<br>@endif
                            @endforeach
                        </div>
                    </td>
                </tr>
            </table>

            <div class="numero">N° ____________ {{ $annee }}/MEF/SG/DGBF/DSP/SRSP.AMM</div>

            <p class="objet">{{ $note['intitule'] }} {{ count($note['agents']) > 1 ? 'aux noms de' : 'au nom de' }} :</p>

            @if (count($note['agents']) > 1)
                <ol class="agents">
                    @foreach ($note['agents'] as $agent)
                        <li>{{ $agent['nom'] }} {{ $agent['prenoms'] }}, -IM :{{ $agent['im'] }}</li>
                    @endforeach
                </ol>
            @else
                <ul class="agents">
                    @foreach ($note['agents'] as $agent)
                        <li>{{ $agent['nom'] }} {{ $agent['prenoms'] }}, -IM :{{ $agent['im'] }}</li>
                    @endforeach
                </ul>
            @endif

            <p class="retour">
                « <strong>Pour Visa</strong> », sauf observation, et retour à
                {{ mb_strtoupper($bordereau->serviceOrigine?->nom ?? '') }}
            </p>

            <div class="signature">
                Ambositra, le<br>
                Le Chef de Service Régional<br>
                de la Solde et des Pensions
            </div>
        </section>
    @endforeach

    <script>
        (function () {
            const destinataires = @json(collect($destinataires)->map(fn ($d) => $d['lignes']));
            const choix = document.getElementById('destinataire');

            if (choix === null) {
                return;
            }

            // Le destinataire change sur toutes les notes sans recharger la page.
            choix.addEventListener('change', function () {
                document.querySelectorAll('.destinataire').forEach(function (bloc) {
                    // Choix vide : chaque note reprend le destinataire de son type.
                    const cle = choix.value === '' ? bloc.dataset.defaut : choix.value;
                    const lignes = destinataires[cle] || [];

                    bloc.innerHTML = lignes
                        .map(function (ligne) {
                            const element = document.createElement('div');
                            element.textContent = ligne;
                            return element.innerHTML;
                        })
                        .join('<br>');
                });
            });
        })();
    </script>
</body>
</html>
