# Déploiement Render — SRSP.AMM

Objectif : `https://srsp-suivi-amm.onrender.com`, accessible même quand le PC est éteint,
avec une base Neon gratuite et permanente et des pièces jointes privées.

Le code de déploiement est dans la branche **`deploy/render`**, dans le dossier
`D:\Projets\suivi-dossiers-render`. L’installation locale `D:\Projets\suivi-dossiers`
reste intacte.

---

## Étape 1 — Créer les comptes

1. Render : https://dashboard.render.com/register
2. Neon : https://neon.com/signup

Aucune carte bancaire n’est demandée sur les plans gratuits.

## Étape 2 — Préparer Neon

1. Créer un projet Neon dans la région **Frankfurt** (`aws-eu-central-1`).
2. Garder la base `neondb`.
3. Dans **Connection Details**, copier la **connection string** Postgres (elle contient le mot de passe).
4. Créer un bucket **Object Storage**.
5. Créer une credential avec les portées `storage:read` et `storage:write`.
   Noter : `token_id`, `s3_secret_access_key`, le nom du bucket et l’endpoint S3.

> Ne collez jamais ces valeurs dans le chat. Elles se mettent directement dans les
> variables d’environnement Render et, localement, dans le `.env` du worktree.

## Étape 3 — Préparer le `.env` local du worktree

Le fichier `D:\Projets\suivi-dossiers-render\.env` existe déjà avec une clé `APP_KEY`.

1. Ouvrir `D:\Projets\suivi-dossiers\.env` et recopier dans le `.env` du worktree
   les lignes `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   (base locale MySQL/XAMPP).
2. Ouvrir `deploy/env.local.example.txt` et recopier les lignes de déploiement
   dans le `.env` du worktree, en remplaçant les valeurs entre guillemets
   par celles données par Neon.

Le fichier `deploy/valeurs-render.secret` contient déjà :
- la valeur `APP_KEY` à mettre dans Render ;
- l’emplacement des nouveaux mots de passe (`deploy/comptes.json`).

## Étape 4 — Créer le schéma et copier les données

Dans un terminal PowerShell :

```powershell
cd D:\Projets\suivi-dossiers-render
php artisan migrate --database=neon --force
php artisan deploy:copier-donnees
php artisan deploy:securiser-comptes
```

- `migrate --database=neon` crée les tables sur Neon.
- `deploy:copier-donnees` copie utilisateurs, divisions, dossiers, pièces,
  historiques, bordereaux et fichiers. La base locale n’est pas modifiée.
- `deploy:securiser-comptes` remplace les 5 mots de passe de test par ceux
  générés dans `deploy/comptes.json`.

Les nouveaux mots de passe sont à lire dans `deploy/comptes.json`.
Conservez ce fichier en lieu sûr puis supprimez-le.

## Étape 5 — Publier la branche

```powershell
cd D:\Projets\suivi-dossiers-render
git add -A
git commit -m "Préparer le déploiement Render avec Neon"
git push -u origin deploy/render
```

## Étape 6 — Créer le service sur Render

1. Dashboard → **New** → **Blueprint** → choisir le dépôt
   `randrianirinaalbert2-cmd/suivi-dossiers`, branche `deploy/render`.
2. Render lit `render.yaml` et crée le service `srsp-suivi-amm` en plan **Free**,
   région Frankfurt, runtime **Docker**.
3. Dans l’onglet **Environment**, remplir les variables marquées « sync: false » :

| Variable | Valeur |
|---|---|
| `APP_KEY` | valeur lue dans `deploy/valeurs-render.secret` |
| `DATABASE_URL` | connection string Neon |
| `AWS_ACCESS_KEY_ID` | `token_id` de la credential Neon |
| `AWS_SECRET_ACCESS_KEY` | `s3_secret_access_key` de la credential Neon |
| `AWS_BUCKET` | nom du bucket |
| `AWS_ENDPOINT_URL_S3` | endpoint S3 Neon |
| `AWS_REGION` | région du bucket |

4. Attendre le premier déploiement (le build Docker prend quelques minutes).

## Étape 7 — Vérifier

- `https://srsp-suivi-amm.onrender.com/health` doit afficher `OK`.
- `https://srsp-suivi-amm.onrender.com/login` doit afficher la page de connexion.
- Se connecter avec un des 5 comptes et son nouveau mot de passe.
- Ouvrir un dossier avec pièces jointes, télécharger un document, vérifier l’historique.
- Tester depuis un téléphone **avec le PC éteint**.

## Limites du plan gratuit Render

- Après 15 minutes sans visite, le service se met en veille. Le premier visiteur
  attend environ 1 minute.
- 750 heures d’utilisation par mois, largement suffisantes pour un usage de bureau.
- Le système de fichiers de Render est éphémère : c’est pourquoi les fichiers
  sont stockés dans Neon Object Storage.

## Sécurité

- Adresse unique, HTTPS obligatoire, cookies sécurisés.
- Pièces jointes et photos servies par des routes authentifiées, jamais par une URL publique.
- Anciens mots de passe remplacés avant la mise en ligne.
- Ne transmettre l’adresse qu’aux personnes autorisées de l’entreprise.

## Retour en arrière

L’installation locale et la branche `main` ne sont pas touchées. Si le déploiement
est abandonné, il suffit de supprimer le service Render et la branche `deploy/render`.
