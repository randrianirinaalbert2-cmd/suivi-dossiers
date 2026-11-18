<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Zone de modification de l'en-tête (à retenir)

Les notes « Soit transmis » (Solde et Visa) et les bordereaux d'envoi reprennent tous leur en-tête et leur référence depuis **un seul endroit** : `config/soit_transmis.php`.

- `entete_administration` : les lignes de l'administration émettrice ;
- `reference_suffixe` : la partie fixe du `N° …` (l'année reste ajoutée automatiquement).

Valeur actuelle : `DIRECTION REGIONALE DU BUDGET ET DES FINANCES AMORON'I MANIA` et `2026/MEF/SG/DGBF/DSP/DRBF.AMM`.

Pour transformer ces textes dans quelques mois ou quelques années, il suffit de modifier ces deux clés. Aucune vue, aucun test ni migration n'est à toucher.

Le **nom de l'application** (titre de la page, navigation et pied de page) se règle dans `APP_NAME` du fichier `.env` (modèle dans `.env.example`). Ces vues lisent `config('app.name')` : une seule ligne est à changer. Le panneau gauche de la page de connexion garde volontairement l'identité `SRSP.AMM`. Valeur actuelle : `SRSP.AMM — Suivi des dossiers Solde et Visa`.

## Accès Internet temporaire (démonstration)

Pour montrer l'application hors du réseau local, un tunnel Cloudflare peut être lancé depuis le poste de développement :

```powershell
.\storage\app\cloudflared.exe tunnel --url http://localhost:8000
```

L'adresse `https://…trycloudflare.com` affichée change à chaque lancement. Le serveur Laravel et le PC doivent rester allumés. Avant toute exposition publique, changer les mots de passe de test et laisser `APP_DEBUG=false`.

Pour obtenir une adresse **lisible et choisie** sans acheter de domaine, localtunnel peut être utilisé :

```powershell
npx --yes localtunnel --port 8000 --subdomain srsp-suivi
```

Elle donne `https://srsp-suivi.loca.lt`, active tant que la commande tourne.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
