<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        {{-- Police chargée sans bloquer l'affichage : sur téléphone, un service externe lent ne doit pas retarder la page. --}}
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                var theme = localStorage.getItem('theme');
                var sombre = theme === 'dark';
                if (sombre) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
    </head>
    <body class="mode-pc font-sans antialiased">
        <div class="min-h-screen fond-page" x-data="{ menuOuvert: window.innerWidth >= 1024 }">
            @include('layouts.navigation')

            <div class="zone-contenu min-w-0 w-full pt-14 transition-[padding-inline-start] duration-200 ease-out" :class="menuOuvert ? 'lg:ps-64' : 'lg:ps-0'">
                {{-- Sur téléphone : même disposition que sur PC, avec défilement horizontal au doigt. --}}
                <div class="mode-pc-defilement">
                    <div class="mode-pc-largeur">
                        <!-- Page Heading -->
                        @isset($header)
                            <header class="entete-page no-print bg-[linear-gradient(90deg,#ffffff_0%,#eef2ff_55%,#faf5ff_100%)] border-b border-indigo-100 shadow-sm">
                                <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
                                    {{ $header }}
                                </div>
                                <div class="bande-soulignement h-1"></div>
                            </header>
                        @endisset

                        @include('partials.flash')

                        <!-- Page Content -->
                        <main>
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Évite les doubles envois (double clic sur « Valider ») : dès que
            // le formulaire part, son bouton est désactivé. Si la navigation
            // est annulée (confirm() refusé), le bouton reste utilisable.
            document.addEventListener('submit', function (evenement) {
                if (evenement.defaultPrevented) {
                    return;
                }

                var formulaire = evenement.target;
                if (! (formulaire instanceof HTMLFormElement)) {
                    return;
                }

                var bouton = evenement.submitter
                    || formulaire.querySelector('button[type="submit"], input[type="submit"]');

                if (! bouton || bouton.disabled) {
                    return;
                }

                window.setTimeout(function () {
                    bouton.disabled = true;
                    bouton.classList.add('opacity-60', 'cursor-wait');
                }, 0);
            });
        </script>
    </body>
</html>
