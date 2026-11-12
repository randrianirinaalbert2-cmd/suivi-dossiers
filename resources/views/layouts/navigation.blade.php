@php
    $utilisateur = Auth::user();
    $estAdmin = $utilisateur->estAdministrateur();
    $roleDivision = $utilisateur->nomRole() . ($utilisateur->division ? ' · ' . $utilisateur->division->nom : '');

    // Les bordereaux de création portent la matricule (IM) des agents : ils
    // ne concernent que la division qui tient la matricule.
    $peutGererBordereauxCreation = $utilisateur->peutGererBordereauxDeCreation();

    $liens = [
        ['route' => 'dashboard', 'libelle' => 'Tableau de bord', 'actif' => 'dashboard', 'icone' => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/>'],
        ['route' => 'bordereaux.index', 'libelle' => 'Bordereaux reçus', 'actif' => ['bordereaux.index', 'bordereaux.show', 'bordereaux.create', 'bordereaux.edit'], 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 14v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v9m0 0 3.5-3.5M12 13l-3.5-3.5"/>'],
        ['route' => 'dossiers.index', 'libelle' => 'Dossiers', 'actif' => 'dossiers.*', 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12V10a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8Z"/>'],
    ];

    if ($peutGererBordereauxCreation) {
        $liens[] = ['route' => 'bordereaux-creation.index', 'libelle' => 'Création de Bordereau', 'actif' => 'bordereaux-creation.*', 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h6.5L18 8v11.5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.5V8H18"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6M9 16.5h4"/>'];
    }

    // Alerte : dossiers rejetés ou incomplets depuis > 72 h (même périmètre
    // de division que le tableau de bord).
    $alerteRejetes = collect();
    $alerteRejetesCount = 0;
    if ($utilisateur) {
        $polesAccessibles = $utilisateur->polesAccessibles();

        $requeteAlertes = \App\Models\Dossier::query()
            ->when(method_exists($utilisateur, 'divisionsAccessibles'), function ($q) use ($utilisateur) {
                $divs = $utilisateur->divisionsAccessibles();
                if ($divs !== null) {
                    $q->whereIn('division_id', $divs);
                }
            })
            // Même périmètre de pôles que le tableau de bord : un compte
            // Solde ne doit pas recevoir d'alerte du pôle Visa (et inversement).
            ->when($polesAccessibles !== null, function ($q) use ($polesAccessibles) {
                if ($polesAccessibles === []) {
                    $q->whereRaw('1 = 0');

                    return;
                }

                $q->where(function ($sous) use ($polesAccessibles) {
                    $sous->whereIn('pole', $polesAccessibles);

                    if (in_array(\App\Enums\PoleDossier::Solde->value, $polesAccessibles, true)) {
                        $sous->orWhereNull('pole');
                    }
                });
            })
            ->whereIn('statut', [\App\Enums\StatutDossier::Rejete->value, \App\Enums\StatutDossier::Incomplet->value])
            ->where(function ($query) {
                $query->where('modifie_le', '<=', now()->subHours(72))
                    ->orWhere(function ($q) {
                        $q->whereNull('modifie_le')->where('cree_le', '<=', now()->subHours(72));
                    });
            });

        $alerteRejetesCount = (clone $requeteAlertes)->count();
        $alerteRejetes = $requeteAlertes
            ->with(['agent', 'division'])
            ->orderByRaw('COALESCE(modifie_le, cree_le) asc')
            ->limit(5)
            ->get();
    }

    $parametrage = [
        ['route' => 'types-dossiers.index', 'libelle' => 'Types de dossiers', 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12V10a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8Z"/>'],
        ['route' => 'pieces-requises.index', 'libelle' => 'Pièces exigées', 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 6h10M10 12h10M10 18h10"/><path stroke-linecap="round" stroke-linejoin="round" d="m4 5 1.3 1.3L7.8 3.8M4 11l1.3 1.3L7.8 9.8M4 17l1.3 1.3L7.8 15.8"/>'],
        ['route' => 'types-pieces.index', 'libelle' => 'Types de pièces', 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h6.5L18 8v11.5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.5V8H18"/>'],
        ['route' => 'agents.index', 'libelle' => 'Agents', 'icone' => '<circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 19.5c1.3-3.3 4-5 7-5s5.7 1.7 7 5"/>'],
        ['route' => 'divisions.index', 'libelle' => 'Divisions', 'icone' => '<rect x="4.5" y="3.5" width="10" height="17" rx="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 8.5H19a1 1 0 0 1 1 1v11M8 7.5h3M8 11.5h3M8 15.5h3"/>'],
        ['route' => 'lieux.index', 'libelle' => 'Lieux', 'icone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-6.5-5.4-6.5-10.5a6.5 6.5 0 1 1 13 0C18.5 15.6 12 21 12 21Z"/><circle cx="12" cy="10.5" r="2.5"/>'],
        ['route' => 'destinations.index', 'libelle' => 'Destinations', 'icone' => '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m0 0-3-3m3 3-3 3"/>'],
        ['route' => 'services-origine.index', 'libelle' => 'Services d\'origine', 'icone' => '<circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8m0 0 3-3m-3 3 3 3"/>'],
        ['route' => 'utilisateurs.index', 'libelle' => 'Utilisateurs', 'icone' => '<circle cx="9.5" cy="8.5" r="3.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19.5c1-3 3.4-4.6 6-4.6s5 1.6 6 4.6"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 5.5a3.2 3.2 0 0 1 0 6M17.5 14.9c1.7.8 2.7 2.2 3 4.6"/>'],
    ];

    $parametrageOuvert = request()->routeIs(
        'divisions.*', 'lieux.*', 'destinations.*', 'services-origine.*',
        'types-dossiers.*', 'types-pieces.*', 'pieces-requises.*', 'agents.*', 'utilisateurs.*'
    );

    // La liste filtrée « Archivage » partage la route dossiers.index avec « Dossiers » :
    // on distingue les deux à l'aide du paramètre de filtre « statut ».
    $surArchives = request()->routeIs('dossiers.index') && request('statut') === 'archive';
    $surArchivesCreation = request()->routeIs('bordereaux-creation.*') && request('statut') === 'archives';
    $archiveOuvert = $surArchives || $surArchivesCreation;
@endphp

<!-- Barre supérieure (tous les écrans) -->
<div class="fixed inset-x-0 top-0 z-50 h-14 bg-[linear-gradient(90deg,#082f49_0%,#0369a1_55%,#0891b2_100%)] flex items-center gap-3 px-4 shadow-lg shadow-black/30 no-print">
    <button type="button" @click="menuOuvert = ! menuOuvert"
            aria-label="Ouvrir ou fermer le menu"
            class="-ms-1 rounded-md p-2 text-indigo-200 transition hover:bg-white/10 hover:text-white">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" :d="menuOuvert ? 'M6 18 18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" />
        </svg>
    </button>
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
        <img src="{{ asset('logo-mef.png') }}" alt="Armoiries — Ministère de l'Économie et des Finances" class="h-9 w-9 shrink-0 rounded-lg bg-white/90 p-0.5 shadow-md shadow-black/30">
        <span class="leading-tight">
            <span class="block text-sm font-semibold text-white">SRSP.AMM — Gestion des dossiers et bordereaux</span>
            <span class="hidden text-xs text-indigo-300 lg:block">de Solde et Visas</span>
        </span>
    </a>
    {{-- Notifications : toujours visibles, la pastille n'apparaît qu'en cas d'alerte --}}
    @php
        $libelleAlerte = $alerteRejetesCount > 0
            ? $alerteRejetesCount.' dossier'.($alerteRejetesCount > 1 ? 's' : '').' rejeté'.($alerteRejetesCount > 1 ? 's' : '').' ou incomplet'.($alerteRejetesCount > 1 ? 's' : '').' depuis plus de 72 h'
            : 'Aucune alerte depuis plus de 72 h';
        $pastilleAlerte = $alerteRejetesCount > 99 ? '99+' : (string) $alerteRejetesCount;
    @endphp

    <div class="relative ms-auto flex shrink-0 items-center gap-1 sm:gap-2"
         x-data="{ notifs: false }" @keydown.escape.window="notifs = false" @click.outside="notifs = false">
            <button type="button"
                    @click="notifs = ! notifs"
                    :aria-expanded="notifs.toString()"
                    aria-haspopup="true"
                    aria-controls="panneau-notifications"
                    title="{{ $libelleAlerte }}"
                    aria-label="Notifications : {{ $libelleAlerte }}"
                    @class([
                        'relative rounded-md p-2 transition hover:bg-white/10',
                        $alerteRejetesCount > 0
                            ? 'text-rose-300 hover:text-rose-100'
                            : 'text-indigo-200 hover:text-white',
                    ])>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                @if ($alerteRejetesCount > 0)
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold leading-none text-white shadow-sm">{{ $pastilleAlerte }}</span>
                @endif
            </button>

            <div x-show="notifs" x-cloak x-transition.opacity.duration.150ms
                 id="panneau-notifications"
                 class="absolute right-0 top-14 z-50 w-80 overflow-hidden rounded-xl border border-gray-200 bg-white text-left shadow-xl shadow-black/20 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notifications</span>
                    @if ($alerteRejetesCount > 0)
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-500/20 dark:text-rose-200">{{ $pastilleAlerte }}</span>
                    @endif
                </div>

                <ul class="max-h-80 divide-y divide-gray-100 overflow-y-auto dark:divide-gray-700">
                    @forelse ($alerteRejetes as $alerte)
                        @php
                            $refAlerte = $alerte->modifie_le ?? $alerte->cree_le;
                            $heuresAlerte = $refAlerte ? (int) $refAlerte->diffInHours(now()) : 0;
                        @endphp
                        <li>
                            <a href="{{ route('dossiers.show', $alerte) }}"
                               class="flex items-start gap-3 px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <span class="mt-1.5 inline-flex h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                                        Dossier #{{ $alerte->id }} — {{ $alerte->agent?->nomComplet() }}
                                    </span>
                                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                        {{ $alerte->statut === \App\Enums\StatutDossier::Rejete ? 'Rejeté' : 'Incomplet' }}
                                        · {{ $heuresAlerte }} h
                                        · {{ $alerte->division?->nom }}
                                    </span>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            Aucune alerte depuis plus de 72 h.
                        </li>
                    @endforelse
                </ul>

                <a href="{{ route('dashboard') }}"
                   class="block border-t border-gray-100 px-4 py-3 text-center text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:border-gray-700 dark:text-indigo-300 dark:hover:bg-gray-700/40">
                    {{ $alerteRejetesCount > $alerteRejetes->count() ? 'Voir les '.$alerteRejetesCount.' alertes' : 'Voir le tableau de bord' }}
                </a>
            </div>

        <a href="{{ route('profile.edit') }}"
           class="hidden items-center gap-2 rounded-full py-1 pe-2 ps-1 text-xs text-indigo-100 transition hover:bg-white/10 hover:text-white sm:flex"
           title="Mon profil">
            <x-avatar :utilisateur="$utilisateur" taille="h-7 w-7" texte="text-[10px]" contour="ring-2 ring-white/30" />
            <span class="max-w-[12rem] truncate">{{ $utilisateur->nom }}</span>
        </a>

        <button type="button"
                x-data="{ sombre: document.documentElement.classList.contains('dark') }"
                @click="sombre = !sombre; document.documentElement.classList.toggle('dark', sombre); localStorage.setItem('theme', sombre ? 'dark' : 'light')"
                aria-label="Basculer entre le mode sombre et le mode clair"
                class="rounded-md p-2 text-indigo-200 transition hover:bg-white/10 hover:text-white">
            <svg class="h-5 w-5" x-show="!sombre" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.9 4.9l1.1 1.1M18 18l1.1 1.1M3 12h1.5M19.5 12H21M4.9 19.1l1.1-1.1M18 6l1.1-1.1"/>
                <circle cx="12" cy="12" r="3.5"/>
            </svg>
            <svg class="h-5 w-5" x-show="sombre" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>
            </svg>
        </button>
    </div>
</div>

<!-- Voile (mobile) -->
<div x-show="menuOuvert" x-cloak x-transition.opacity
     class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden no-print"
     @click="menuOuvert = false"></div>

<!-- Menu latéral gauche -->
<aside x-cloak
       class="no-print fixed bottom-0 left-0 top-14 z-40 flex w-64 flex-col bg-[linear-gradient(180deg,#082f49_0%,#075985_45%,#0e7490_100%)] shadow-xl shadow-black/30 transition-transform duration-200 ease-out"
       :class="menuOuvert ? 'translate-x-0' : '-translate-x-full'">

    <div class="flex h-14 shrink-0 items-center justify-between border-b border-white/10 px-4">
        <span class="text-sm font-semibold text-white">Menu</span>
        <button type="button" @click="menuOuvert = false" aria-label="Fermer le menu"
                class="rounded-md p-1.5 text-indigo-300 transition hover:bg-white/10 hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @foreach ($liens as $lien)
            @php($lienActif = request()->routeIs(...(array) $lien['actif'])
                && ! ($lien['route'] === 'dossiers.index' && $surArchives)
                && ! ($lien['route'] === 'bordereaux-creation.index' && $surArchivesCreation))
            <a href="{{ route($lien['route']) }}"
               @click="if(window.innerWidth < 1024) menuOuvert = false"
               @class([
                   'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                   $lienActif
                   ? 'bg-[linear-gradient(90deg,#0284c7,#06b6d4)] text-white shadow-md shadow-black/25'
                   : 'text-indigo-200 hover:bg-white/10 hover:text-white',
           ])>
            <svg class="h-5 w-5 shrink-0 transition"
                 @class([$lienActif ? 'text-white' : 'text-indigo-300 group-hover:text-white'])
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    {!! $lien['icone'] !!}
                </svg>
                <span>{{ $lien['libelle'] }}</span>
            </a>
        @endforeach

        {{-- Archivage : distinguer les bordereaux de création des bordereaux avec dossier --}}
        <div class="pt-1" x-data="{ ouvert: {{ $archiveOuvert ? 'true' : 'false' }} }">
            <button type="button" @click.stop="ouvert = ! ouvert"
                    @class([
                        'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                        $archiveOuvert ? 'text-white' : 'text-indigo-200 hover:bg-white/10 hover:text-white',
                    ])>
                <svg class="h-5 w-5 shrink-0" @class([$archiveOuvert ? 'text-indigo-200' : 'text-indigo-300 group-hover:text-white'])
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16M5.5 7.5V18a1.5 1.5 0 0 0 1.5 1.5h10a1.5 1.5 0 0 0 1.5-1.5V7.5M6.5 7.5V5.5A1.5 1.5 0 0 1 8 4h8a1.5 1.5 0 0 1 1.5 1.5v2M9.5 11.5h5"/>
                </svg>
                <span class="flex-1 text-start">Archives</span>
                <svg class="h-4 w-4 shrink-0 text-indigo-400 transition-transform duration-200"
                     :class="ouvert ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
            </button>

            <div x-show="ouvert" x-cloak x-transition.duration.150ms
                 class="ms-3 mt-2 space-y-1 rounded-xl border border-white/10 bg-white/5 p-2 shadow-inner shadow-black/10">
                @if ($peutGererBordereauxCreation)
                    <a href="{{ route('bordereaux-creation.index', ['statut' => 'archives']) }}"
                       @click="if(window.innerWidth < 1024) menuOuvert = false"
                       @class([
                           'group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-sm transition',
                           $surArchivesCreation
                               ? 'bg-cyan-400/20 font-medium text-white ring-1 ring-cyan-300/30'
                               : 'text-cyan-100/80 hover:bg-white/10 hover:text-white',
                       ])>
                        <svg class="h-4 w-4 shrink-0" @class([$surArchivesCreation ? 'text-cyan-200' : 'text-cyan-300/80 group-hover:text-white'])
                             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h6.5L18 8v11.5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.5V8H18"/>
                        </svg>
                        <span class="truncate">Bordereau Créé</span>
                    </a>
                @endif
                <a href="{{ route('dossiers.index', ['statut' => 'archive']) }}"
                   @click="if(window.innerWidth < 1024) menuOuvert = false"
                   @class([
                       'group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-sm transition',
                       $surArchives
                           ? 'bg-cyan-400/20 font-medium text-white ring-1 ring-cyan-300/30'
                           : 'text-cyan-100/80 hover:bg-white/10 hover:text-white',
                   ])>
                    <svg class="h-4 w-4 shrink-0" @class([$surArchives ? 'text-cyan-200' : 'text-cyan-300/80 group-hover:text-white'])
                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V10a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8Z"/>
                    </svg>
                    <span class="truncate">Bordereaux avec dossier</span>
                </a>
            </div>
        </div>

        <a href="{{ route('statistiques.dossiers') }}"
           @click="if(window.innerWidth < 1024) menuOuvert = false"
           @class([
               'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
               request()->routeIs('statistiques.*')
               ? 'bg-[linear-gradient(90deg,#0284c7,#06b6d4)] text-white shadow-md shadow-black/25'
               : 'text-indigo-200 hover:bg-white/10 hover:text-white',
           ])>
            <svg class="h-5 w-5 shrink-0 transition"
                 @class([request()->routeIs('statistiques.*') ? 'text-white' : 'text-indigo-300 group-hover:text-white'])
                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
            </svg>
            <span>Statistiques des Dossiers</span>
        </a>

        @if ($estAdmin)
            <div class="pt-3" x-data="{ ouvert: {{ $parametrageOuvert ? 'true' : 'false' }} }">
                <button type="button" @click.stop="ouvert = ! ouvert"
                        @class([
                            'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                            $parametrageOuvert ? 'text-white' : 'text-indigo-200 hover:bg-white/10 hover:text-white',
                        ])>
                    <svg class="h-5 w-5 shrink-0" @class([$parametrageOuvert ? 'text-indigo-200' : 'text-indigo-300 group-hover:text-white'])
                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 17h16"/>
                        <circle cx="9" cy="7" r="2.4"/>
                        <circle cx="15" cy="17" r="2.4"/>
                    </svg>
                    <span class="flex-1 text-start">Paramétrage</span>
                    <svg class="h-4 w-4 shrink-0 text-indigo-400 transition-transform duration-200"
                         :class="ouvert ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div x-show="ouvert" x-cloak x-transition.duration.150ms
                     class="ms-3 mt-2 space-y-1 rounded-xl border border-white/10 bg-white/5 p-2 shadow-inner shadow-black/10">
                    @foreach ($parametrage as $item)
                        <a href="{{ route($item['route']) }}"
                           @click="if(window.innerWidth < 1024) menuOuvert = false"
                           @class([
                               'group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-sm transition',
                               request()->routeIs($item['route'])
                                   ? 'bg-cyan-400/20 font-medium text-white ring-1 ring-cyan-300/30'
                                   : 'text-cyan-100/80 hover:bg-white/10 hover:text-white',
                           ])>
                            <svg class="h-4 w-4 shrink-0"
                                 @class([request()->routeIs($item['route']) ? 'text-cyan-200' : 'text-cyan-300/80 group-hover:text-white'])
                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                {!! $item['icone'] !!}
                            </svg>
                            <span class="truncate">{{ $item['libelle'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </nav>

    <div class="shrink-0 border-t border-white/10 p-3">
        <div class="flex items-center gap-3 px-1">
            <a href="{{ route('profile.edit') }}" class="shrink-0 rounded-full transition hover:opacity-90" title="Mon profil">
                <x-avatar :utilisateur="$utilisateur" taille="h-9 w-9" texte="text-sm" contour="ring-2 ring-white/20 shadow-md shadow-black/30" />
            </a>
            <div class="min-w-0">
                <div class="truncate text-sm font-medium text-white">{{ $utilisateur->nom }}</div>
                <div class="truncate text-xs text-indigo-300">{{ $roleDivision }}</div>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <a href="{{ route('profile.edit') }}"
               class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-white/15 px-3 py-1.5 text-sm text-indigo-200 transition hover:bg-white/10 hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <circle cx="12" cy="8" r="3.5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 19.5c1.3-3.3 4-5 7-5s5.7 1.7 7 5"/>
                </svg>
                Profil
            </a>

            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-white/15 px-3 py-1.5 text-sm text-indigo-200 transition hover:border-rose-400/40 hover:bg-rose-500/20 hover:text-rose-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H4m0 0 3.5-3.5M4 12l3.5 3.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 4h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-8"/>
                    </svg>
                    Quitter
                </button>
            </form>
        </div>
    </div>
</aside>
