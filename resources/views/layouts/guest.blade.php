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
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts / Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                var theme = localStorage.getItem('theme');
                var sombre = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (sombre) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">

            <!-- Panneau décoré (gauche) -->
            <div class="relative hidden overflow-hidden bg-[radial-gradient(120%_120%_at_0%_0%,#0ea5e9_0%,#0c4a6e_45%,#082f49_100%)] p-10 text-white lg:flex lg:flex-col lg:justify-center xl:p-14">
                <div class="pointer-events-none absolute -left-32 -top-40 h-[28rem] w-[28rem] rounded-full bg-cyan-400/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -right-28 top-1/2 h-[26rem] w-[26rem] -translate-y-1/2 rounded-full bg-sky-400/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-40 left-1/4 h-[30rem] w-[30rem] rounded-full bg-teal-300/10 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
                     style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 26px 26px;"></div>
                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,transparent_0%,rgba(8,47,73,.4)_100%)]"></div>

                <div class="relative z-10 flex h-full flex-col justify-center">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('logo-mef.png') }}" alt="Armoiries — Ministère de l'Économie et des Finances"
                             class="h-16 w-16 shrink-0 rounded-2xl bg-white/95 p-1.5 shadow-2xl shadow-black/40 ring-1 ring-white/60">
                        <div class="leading-tight">
                            <span class="block text-lg font-bold tracking-wide text-white">SRSP.AMM — Service Régional de la Solde et des Pensions</span>
                            <span class="block text-sm text-cyan-200/90">Amoron'i Mania</span>
                        </div>
                    </div>

                    <div class="mt-12 max-w-xl">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.25em] text-cyan-200 shadow-lg shadow-cyan-500/10 backdrop-blur">
                            <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            Application interne
                        </div>
                        <h1 class="text-4xl font-bold leading-tight xl:text-5xl">
                            Gestion des dossiers
                            <span class="bg-[linear-gradient(90deg,#67e8f9,#22d3ee,#38bdf8)] bg-clip-text text-transparent">Solde et Visa</span>
                        </h1>
                        <p class="mt-4 max-w-lg text-base leading-relaxed text-sky-100/90">
                            Suivi, traçabilité et archivage des dossiers du SRSP.AMM.
                        </p>
                    </div>

                    <div class="mt-12 flex flex-wrap items-center justify-between gap-4 text-xs text-cyan-200/80">
                        <span>© {{ now()->year }} — {{ config('app.name') }}</span>
                        <span>SRSP.AMM — Amoron'i Mania</span>
                    </div>
                </div>
            </div>

            <!-- Formulaire (droite) -->
            <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f0f9ff] px-5 py-10 sm:px-8 lg:h-screen lg:overflow-y-auto">
                <button type="button"
                        x-data="{ sombre: document.documentElement.classList.contains('dark') }"
                        @click="sombre = !sombre; document.documentElement.classList.toggle('dark', sombre); localStorage.setItem('theme', sombre ? 'dark' : 'light')"
                        aria-label="Basculer entre le mode sombre et le mode clair"
                        class="absolute right-5 top-5 z-20 inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-white/75 px-3 py-1.5 text-xs font-medium text-indigo-700 shadow-sm backdrop-blur transition hover:scale-105 dark:border-white/15 dark:bg-white/10 dark:text-indigo-200">
                    <svg class="h-4 w-4" x-show="!sombre" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.9 4.9l1.1 1.1M18 18l1.1 1.1M3 12h1.5M19.5 12H21M4.9 19.1l1.1-1.1M18 6l1.1-1.1"/>
                        <circle cx="12" cy="12" r="3.5"/>
                    </svg>
                    <svg class="h-4 w-4" x-show="sombre" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>
                    </svg>
                    <span x-show="!sombre" x-cloak>Sombre</span>
                    <span x-show="sombre" x-cloak>Clair</span>
                </button>
                <!-- Fond : halos colorés -->
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(45rem_45rem_at_10%_-10%,rgba(14,165,233,.28),transparent_60%),radial-gradient(40rem_40rem_at_105%_15%,rgba(6,182,212,.24),transparent_55%),radial-gradient(38rem_38rem_at_50%_115%,rgba(34,211,238,.20),transparent_60%)]"></div>

                <!-- Fond : trame fine -->
                <div class="pointer-events-none absolute inset-0 opacity-40"
                     style="background-image: linear-gradient(rgba(14,165,233,.10) 1px, transparent 1px), linear-gradient(90deg, rgba(14,165,233,.10) 1px, transparent 1px); background-size: 34px 34px; -webkit-mask-image: radial-gradient(circle at 50% 40%, black, transparent 80%); mask-image: radial-gradient(circle at 50% 40%, black, transparent 80%);"></div>

                <!-- Formes flottantes -->
                <div class="pointer-events-none absolute left-[6%] top-[14%] h-24 w-24 rotate-12 rounded-[2rem] bg-[linear-gradient(135deg,rgba(14,165,233,.45),rgba(6,182,212,.30))] blur-[1px] animate-pulse" style="animation-duration:6s"></div>
                <div class="pointer-events-none absolute right-[8%] top-[60%] h-16 w-16 -rotate-12 rounded-full bg-[linear-gradient(135deg,rgba(34,211,238,.40),rgba(45,212,191,.30))] blur-[1px] animate-pulse" style="animation-duration:7s"></div>
                <div class="pointer-events-none absolute bottom-[8%] left-[14%] h-20 w-20 rotate-6 rounded-3xl border-2 border-sky-400/40 animate-pulse" style="animation-duration:8s"></div>

                <div class="relative z-10 w-full max-w-lg">

                    <!-- Marque -->
                    <div class="mb-6 flex items-center justify-center gap-3">
                        <span class="relative inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/70 bg-[linear-gradient(135deg,#0ea5e9,#06b6d4)] text-white shadow-xl shadow-indigo-500/40 ring-4 ring-white/70">
                            <x-icon.folder class="h-6 w-6" />
                        </span>
                        <div class="leading-tight">
                            <span class="block text-base font-bold text-gray-900">Gestion des dossiers, bordereaux</span>
                            <span class="block text-xs font-medium uppercase tracking-wider text-indigo-500">de Solde et Visas</span>
                        </div>
                    </div>

                    <!-- Carte du formulaire -->
                    <div class="relative">
                        <div class="pointer-events-none absolute -inset-3 rounded-[2rem] bg-[linear-gradient(135deg,rgba(14,165,233,.35),rgba(6,182,212,.25),rgba(34,211,238,.20))] blur-xl opacity-70"></div>

                        <div class="relative overflow-hidden rounded-3xl border border-white/70 bg-white/85 shadow-2xl shadow-sky-950/10 backdrop-blur-xl">
                            <div class="h-1.5 bg-[linear-gradient(90deg,#0ea5e9,#06b6d4,#22d3ee)]"></div>

                            <div class="p-6 sm:p-8">
                                {{ $slot }}
                            </div>
                        </div>
                    </div>

                    <!-- Barre de confiance -->
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                            Connexion sécurisée
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.8"/></svg>
                            Accès 24h/24
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                            Traçabilité
                        </span>
                    </div>

                    <p class="mt-3 text-center text-xs text-gray-400">
                        Application interne — accès réservé aux utilisateurs autorisés
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
