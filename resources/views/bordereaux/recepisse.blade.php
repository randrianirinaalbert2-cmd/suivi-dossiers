<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Récépissé de saisie — {{ $bordereau->reference }}</title>
    <style>
        @page { size: A4 portrait; margin: 16mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #000; margin: 24px auto; max-width: 760px; }
        .titre { text-align: center; font-size: 16px; margin-bottom: 4px; }
        .soustitre { text-align: center; font-size: 11.5px; color: #444; margin-bottom: 18px; }
        .regle { border: 0; border-top: 2px solid #000; margin: 0 0 16px; }
        .regle-fine { border: 0; border-top: 1px solid #000; margin: 14px 0; }
        table.infos { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.infos td { border: 1px solid #000; padding: 4px 8px; font-size: 12.5px; vertical-align: middle; }
        table.infos td.cle { width: 140px; text-transform: uppercase; font-size: 11px; background: #f3f4f6; }
        table.liste { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.liste th { border: 1px solid #000; padding: 4px; font-weight: normal; text-transform: uppercase; font-size: 11.5px; background: #f3f4f6; }
        table.liste td { border: 1px solid #000; padding: 3px 6px; vertical-align: top; font-size: 12.5px; }
        .centre { text-align: center; }
        .haut { height: 26px; line-height: 26px; }
        .mention { margin-top: 20px; padding: 8px 10px; border: 1px dashed #6b7280; font-size: 11.5px; font-style: italic; color: #374151; text-align: center; }
        .pied { margin-top: 18px; display: flex; justify-content: space-between; font-size: 11.5px; }
        .actions { margin-top: 24px; text-align: center; }
        @media print { body { margin: 0; max-width: none; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="titre"><strong>RÉCÉPISSÉ DE SAISIE</strong></div>
    <div class="soustitre">Bordereau reçu — enregistrement dans le système de suivi</div>
    <hr class="regle">

    <table class="infos">
        <tr>
            <td class="cle">Référence</td>
            <td>{{ $bordereau->reference }}</td>
            <td class="cle">Objet</td>
            <td>{{ $bordereau->objet ?: '—' }}</td>
        </tr>
        <tr>
            <td class="cle">Service d'origine</td>
            <td>{{ $bordereau->serviceOrigine?->nom }}</td>
            <td class="cle">Division</td>
            <td>{{ $bordereau->division?->nom }}</td>
        </tr>
        <tr>
            <td class="cle">Localité</td>
            <td>{{ $bordereau->lieu ? $bordereau->lieu->code.' — '.$bordereau->lieu->nom : '—' }}</td>
            <td class="cle">Dossiers annoncés</td>
            <td>{{ $bordereau->nombre_dossiers_attendus ?? '—' }}
                @if ($bordereau->nombre_dossiers_attendus !== null)
                    ({{ $bordereau->nombreDossiersSaisis() }} saisi(s))
                @endif
            </td>
        </tr>
        <tr>
            <td class="cle">Date du bordereau</td>
            <td>{{ $bordereau->date_bordereau?->format('d/m/Y') }}</td>
            <td class="cle">Numéro d'arrivée</td>
            <td>{{ $bordereau->num_arrivee }}</td>
        </tr>
        <tr>
            <td class="cle">Date d'arrivée</td>
            <td>{{ $bordereau->date_arrivee?->format('d/m/Y') }}</td>
            <td class="cle">Saisi par</td>
            <td>{{ $bordereau->utilisateur?->nom }}</td>
        </tr>
    </table>

    <table class="liste">
        <thead>
            <tr>
                <th style="width: 32px;">N°</th>
                <th>Nom et prénoms</th>
                <th style="width: 82px;">IM</th>
                <th style="width: 62px;">CMVT</th>
                <th style="width: 110px;">Statut</th>
                <th style="width: 140px;">Responsable</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bordereau->dossiers as $index => $dossier)
                <tr>
                    <td class="centre haut">{{ $index + 1 }}</td>
                    <td class="haut">{{ mb_strtoupper(trim(($dossier->agent?->nom ?? '').' '.($dossier->agent?->prenoms ?? ''))) }}</td>
                    <td class="centre haut">{{ $dossier->agent?->im }}</td>
                    <td class="centre haut">{{ $dossier->typeDossier?->code }}</td>
                    <td class="centre haut">{{ $dossier->statut->label() }}</td>
                    <td class="haut">{{ $dossier->responsable?->nom }}</td>
                </tr>
            @empty
                <tr>
                    <td class="centre" colspan="6" style="height: 90px; vertical-align: middle;">Aucun dossier rattaché</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($bordereau->observation)
        <hr class="regle-fine">
        <div><strong>Observation :</strong> {{ $bordereau->observation }}</div>
    @endif

    <div class="mention">
        Document édité par le système de suivi des dossiers et bordereaux.<br>
        Il ne remplace pas le bordereau papier d'origine transmis par le service d'origine.
    </div>

    <div class="pied">
        <span>Édité le {{ now()->format('d/m/Y à H:i') }}</span>
        <span>{{ auth()->user()?->nom }}</span>
    </div>

    <div class="actions no-print">
        <button onclick="window.print()" style="padding: 8px 18px; cursor: pointer;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" style="width:16px;height:16px;vertical-align:-3px;margin-right:6px" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>Imprimer</button>
    </div>
</body>
</html>
