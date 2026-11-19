# Guide d'auto-formation — 7 jours pour devenir autonome

Objectif : faire vous-même, sans hésiter, ce que je fais aujourd'hui en quelques secondes.
**20 à 30 minutes par jour suffisent.** Ne cherchez pas à tout comprendre du premier coup :
l'important est de répéter les mêmes gestes jusqu'à ce qu'ils deviennent automatiques.

---

## Règles d'or (à lire une fois, à respecter toujours)

1. **Une seule modification à la fois.** Si ça casse, vous savez où regarder.
2. **Ne jamais envoyer** `database/seeders/VisaSeeder.php` (modification locale à préserver),
   `.env`, `public/build`, `vendor/`.
3. **Ne jamais lancer Vite** (`npm run dev`). Utiliser seulement `npm run build` quand le CSS/JS change,
   et vérifier que `public\hot` n'existe pas.
4. **PowerShell n'accepte pas `&&`** : utiliser `;` pour enchaîner deux commandes.
5. **Pas de migration sans accord** préalable (elle modifie la base réelle).
6. **Avant chaque modification** : `git status --short` pour voir l'état actuel.
7. **Après chaque modification** : tester, puis enregistrer.

---

## Préparation (10 minutes) — travailler sur une copie

Pour apprendre sans risque, faites une copie du projet. Vos erreurs n'auront aucun effet sur le site.

```powershell
Copy-Item -Path D:\Projets\suivi-dossiers -Destination D:\Projets\suivi-dossiers-entrainement -Recurse -Force
Set-Location D:\Projets\suivi-dossiers-entrainement
```

La copie contient son propre historique Git : vos commits d'entraînement resteront dans la copie.

---

## Jour 1 — Savoir où l'on est (20 min)

**Objectif :** lire l'état du projet et son historique.

```powershell
Set-Location D:\Projets\suivi-dossiers-entrainement
Get-Location
Get-ChildItem -Name
git status --short
git log --oneline -5
```

**À observer :**
- `git status --short` affiche ` M database/seeders/VisaSeeder.php` : c'est la modification locale
  qu'on ne commit **jamais**. C'est normal, laissez-la.
- `git log --oneline -5` affiche les derniers commits, le plus récent en haut.
- `M` = modifié, `??` = nouveau fichier, `A` = ajouté à Git.

**Exercice :** notez sur un papier la signification de `M`, `??` et `A`.

---

## Jour 2 — Faire son premier commit (25 min)

**Objectif :** modifier → voir la différence → enregistrer.

1. Ouvrez `resources/views/auth/login.blade.php`.
2. Trouvez le texte `Bienvenue ! Connectez-vous...` et remplacez-le par `Bienvenue chez vous ! Connectez-vous...`.
3. Dans PowerShell :

```powershell
git diff -- resources\views\auth\login.blade.php
php artisan test --filter="NomApplicationTest"
git add resources\views\auth\login.blade.php
git status --short
git commit -m "Modifier le message d'accueil"
git log --oneline -3
```

**À observer :**
- `git diff` montre exactement la ligne changée (le `-` = avant, le `+` = après).
- Après `git add`, la lettre passe de ` M` à `M ` (indexé).
- Après `git commit`, `git status` ne montre plus ce fichier.

**Exercice :** refaites la même chose avec un autre texte de la même vue, avec un autre message de commit.

---

## Jour 3 — Annuler sans casser (20 min)

**Objectif :** savoir revenir en arrière quand on s'est trompé.

1. Modifiez encore le message d'accueil, n'importe comment.
2. Vérifiez :

```powershell
git status --short
git diff -- resources\views\auth\login.blade.php
```

3. Annulez **uniquement ce fichier** :

```powershell
git restore -- resources\views\auth\login.blade.php
git status --short
```

**À retenir :**
- `git restore -- fichier` annule les modifications **non enregistrées** d'un fichier.
- `git revert <hash>` annule un commit **déjà envoyé** (plus avancé : demandez-moi avant de l'utiliser).
- Soyez précis : `git restore -- .` annulerait **tous** les fichiers modifiés.

**Exercice :** modifiez deux fichiers, puis annulez-en un seul.

---

## Jour 4 — Comprendre le chemin d'une page (30 min)

**Objectif :** savoir où regarder quand quelque chose ne va pas.

Une page suit toujours ce chemin :

```text
routes/web.php  →  contrôleur  →  vue Blade  →  navigateur
```

Pour le tableau de bord :

1. `routes/web.php` : trouvez `Route::get('dashboard', DashboardController::class)->name('dashboard');`
2. `app/Http/Controllers/DashboardController.php` : voyez quelles données sont envoyées à la vue.
3. `resources/views/dashboard.blade.php` : trouvez le texte `Situation au`.

**Vocabulaire Blade :**
- `{{ $variable }}` affiche une valeur ;
- `@foreach ($liste as $item)` répète un bloc ;
- `@if (...)` affiche sous condition ;
- `@extends('layouts.app')` utilise le gabarit commun.

**Exercice :** changez un mot dans `dashboard.blade.php`, lancez
`php artisan test --filter="NomApplicationTest"`, puis commitez.

---

## Jour 5 — Lire un test (30 min)

**Objectif :** ne plus avoir peur de la suite de tests.

```powershell
php artisan test --filter="NomApplicationTest"
php artisan test
```

**À observer :**
- Le test ciblé affiche le nombre de tests et de vérifications réussis.
- La suite complète affiche **169 tests, 166 réussis, 3 échecs connus** :
  `BordereauRecuImpressionTest` et deux tests de `DossierStatutPermissionTest`.
  Ce sont les seuls échecs attendus — si vous en voyez un 4ᵉ, c'est une régression.

Ouvrez `tests/Feature/SecuriteDeconnexionTest.php` : un test se lit toujours en trois temps :

```php
$utilisateur = ...;              // 1. préparer ( arrangements )
$this->actingAs($utilisateur)... // 2. agir
    ->assertRedirect(route('login')); // 3. vérifier ( assertions )
```

**Exercice :** expliquez avec vos mots ce que vérifie
`test_apres_deconnexion_un_retour_vers_le_tableau_de_bord_renvoie_vers_la_connexion`.

---

## Jour 6 — La routine de déploiement complète (30 min)

**Objectif :** enchaîner les 5 étapes sur le **vrai** projet.

Faites une modification minuscule (un mot dans un titre), puis suivez exactement ceci :

```powershell
Set-Location D:\Projets\suivi-dossiers

# 1. État avant
git status --short

# 2. Tests ciblés
php artisan test --filter="NomApplicationTest"

# 3. Recompiler SEULEMENT si CSS / JS / classe Tailwind modifiée
npm run build

# 4. Envoyer dans Git (sans VisaSeeder.php, .env ni public/build)
git add resources\views\...\votre-fichier.blade.php
git status --short
git commit -m "Décrire la modification en une phrase"

# 5. Publier
git push origin main
```

Puis : `Ctrl + F5` sur PC, nouvel onglet sur téléphone, et test.

**Si vous avez modifié du PHP** : ajoutez avant les tests
`.\vendor\bin\pint chemin\du\fichier.php` pour formater le code.

**Important :** une classe Tailwind nouvelle (par exemple `min-w-[640px]`) **doit** être recompilée.
Sans `npm run build`, le style n'existe pas et le texte peut devenir invisible.

---

## Jour 7 — Hébergement, sécurité et sauvegarde (30 min)

**Objectif :** savoir ce qui fait tourner le site et le protéger.

Lisez la section « Accès Internet temporaire » du `README.md`. Le site dépend de 4 éléments :

| Élément | Commande / service | Nécessaire pour |
|---|---|---|
| PC allumé, pas en veille | — | tout |
| Base XAMPP (MySQL/MariaDB) | panneau XAMPP → Start sur MySQL | tout |
| Serveur Laravel | `php artisan serve --host=0.0.0.0 --port=8000` | tout |
| Tunnel public | `npx --yes localtunnel --port 8000 --subdomain srsp-suivi-dossiers` | accès hors Wi-Fi |

**Exercice sauvegarde (à faire une fois par semaine) :**
1. Ouvrez `http://localhost/phpmyadmin`.
2. Sélectionnez la base `suivi_dossiers`.
3. Onglet **Exporter** → **Exporter** → enregistrez `suivi_dossiers_AAAA-MM-JJ.sql`.
4. Rangez ce fichier **hors du projet** (par exemple dans `Documents\Sauvegardes`).
   Ne le commitez jamais : il contient des données réelles.

**Pour aller plus loin plus tard :** louer un petit serveur en ligne (VPS), y installer PHP + MySQL,
pointer un nom de domaine, activer HTTPS, programmer des sauvegardes. Le code est déjà prêt.

---

## Checklist imprimable (à recopier près du clavier)

```text
[ ] git status --short
[ ] Une seule modification
[ ] Pint si PHP        : .\vendor\bin\pint <fichier.php>
[ ] Tests              : php artisan test --filter="<NomDuTest>"
[ ] Build si CSS/JS    : npm run build   (jamais npm run dev)
[ ] git add <fichiers> puis git status --short
[ ] git commit -m "<phrase courte>"
[ ] git push origin main
[ ] Ctrl + F5 + test sur téléphone
```

## Si quelque chose ne va pas

| Symptôme | Réflexe |
|---|---|
| Ancien style / page blanche | `npm run build`, puis `Ctrl + F5` |
| Erreur 500 | `Get-Content storage\logs\laravel.log -Tail 30` |
| Je ne sais plus ce que j'ai modifié | `git status --short` puis `git diff` |
| Je veux annuler un fichier | `git restore -- <fichier>` |
| 4ᵉ échec dans les tests | lire le message : c'est une régression, me le signaler |
| Site inaccessible | vérifier XAMPP → MySQL, puis `php artisan serve`, puis le tunnel |
| `public\hot` existe | le supprimer : il force Vite et casse l'affichage |

---

## Vocabulaire express

| Mot | Signification simple |
|---|---|
| **commit** | une photo enregistrée du projet à un instant T |
| **push** | envoyer vos photos sur GitHub |
| **middleware** | un filtre qui agit avant/après chaque page (sécurité, cache…) |
| **migration** | une modification de la structure de la base de données |
| **Blade** | le moteur de gabarits qui fabrique le HTML |
| **build** | fabriquer les fichiers CSS/JS optimisés dans `public/build` |

---

Quand vous butez sur une ligne ou une commande, copiez-la ici avec le message d'erreur : je vous
explique le **pourquoi**, pas seulement la solution. C'est comme ça qu'on devient autonome.
