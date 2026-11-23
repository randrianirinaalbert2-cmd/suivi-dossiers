<?php

/**
 * Génère, dans la feuille de style compilée, une version « PC » des classes
 * responsive Tailwind pour les téléphones.
 *
 * Principe : les pages utilisent des classes sm:/md:/lg:/xl: qui, sur un
 * téléphone, ne s'appliquent pas (la largeur est de ~390 px). On recopie ces
 * règles dans un bloc @media (width<=1023px), limité au<body class="mode-pc">
 * de l'application. La largeur utile est fixée à 1180 px dans un conteneur à
 * défilement horizontal : on voit la même disposition que sur ordinateur et
 * on fait glisser le doigt pour voir la suite.
 *
 * À relancer uniquement si public/build est reconstruit (ce qui ne doit pas
 * être fait ici : la version compilée est fournie et versionnée).
 */

$racine = dirname(__DIR__);
$manifest = json_decode((string) file_get_contents($racine.'/public/build/manifest.json'), true);
$cssRelatif = $manifest['resources/css/app.css']['file'] ?? null;

if (! $cssRelatif) {
    fwrite(STDERR, "Fichier CSS introuvable dans le manifeste.\n");
    exit(1);
}

$chemin = $racine.'/public/build/'.$cssRelatif;
$css = (string) file_get_contents($chemin);

// Supprime un bloc précédent pour rendre le script idempotent.
$css = preg_replace('~/\* DEBUT MODE PC MOBILE \*/.*?/\* FIN MODE PC MOBILE \*/~s', '', $css);

function finBloc(string $css, int $debut): int
{
    $profondeur = 0;
    $chaine = false;
    $guillemet = '';

    for ($i = $debut; $i < strlen($css); $i++) {
        $c = $css[$i];
        if ($chaine) {
            if ($c === '\\') { $i++; continue; }
            if ($c === $guillemet) { $chaine = false; }
            continue;
        }
        if ($c === '"' || $c === "'") { $chaine = true; $guillemet = $c; continue; }
        if ($c === '{') { $profondeur++; }
        elseif ($c === '}') { $profondeur--; if ($profondeur === 0) { return $i; } }
    }

    return -1;
}

$regles = [];
$position = 0;

while (($pos = strpos($css, '@media (width>=', $position)) !== false) {
    $ouverture = strpos($css, '{', $pos);
    $fin = finBloc($css, $ouverture);
    if ($ouverture === false || $fin < 0) { break; }

    $contenu = substr($css, $ouverture + 1, $fin - $ouverture - 1);
    preg_match_all('/([^{}]+)\{([^{}]*)\}/s', $contenu, $trouver, PREG_SET_ORDER);

    foreach ($trouver as $regle) {
        $selecteurs = array_filter(array_map('trim', explode(',', $regle[1])));
        $prefixes = [];
        foreach ($selecteurs as $selecteur) {
            $prefixes[] = ':where(.mode-pc) '.$selecteur;
        }
        $regles[] = implode(', ', $prefixes).'{'.$regle[2].'}';
    }

    $position = $fin + 1;
}

$bloc = "/* DEBUT MODE PC MOBILE */\n"
    ."@media (width<=1023px){\n"
    .":where(.mode-pc) .mode-pc-defilement{overflow-x:auto;-webkit-overflow-scrolling:touch;overscroll-behavior-x:contain;}\n"
    .":where(.mode-pc) .mode-pc-largeur{min-width:1180px;}\n"
    .implode("\n", $regles)."\n"
    // Le menu latéral reste un tiroir sur téléphone : il ne doit pas réduire
    // la largeur de la zone à défilement.
    .":where(.mode-pc) .zone-contenu.lg\\:ps-64{padding-inline-start:0;}\n"
    ."}\n"
    ."/* FIN MODE PC MOBILE */\n";

file_put_contents($chemin, rtrim($css)."\n".$bloc);

echo 'Mode PC mobile généré : '.count($regles)." règles dans ".basename($chemin)."\n";
