<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Situation au {{ now()->format('d/m/Y') }} — division
                    {{ Auth::user()->voitToutesLesDivisions() ? 'toutes' : (Auth::user()->division?->nom ?? 'non définie') }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('bordereaux.create') }}"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                    Nouveau bordereau reçu
                </a>
                <a href="{{ route('dossiers.create') }}"
                   class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                    Nouveau dossier
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-6">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            @if(isset($dossiersTraitesAujourdhui) && $dossiersTraitesAujourdhui > 0)
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2 text-sm text-emerald-800">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <span><strong>{{ $dossiersTraitesAujourdhui }}</strong> dossier(s) traité(s) / vérifié(s) / transmis aujourd'hui</span>
                    </div>
                </div>
            @endif
            {{-- Cartes de synthèse --}}
            <div @class([
                'grid grid-cols-1 gap-4 sm:grid-cols-2',
                'lg:grid-cols-5' => $voitVisa,
                'lg:grid-cols-4' => ! $voitVisa,
            ])>
                <div class="rounded-xl border border-indigo-100 bg-[linear-gradient(135deg,#eef2ff_0%,#ffffff_75%)] p-5 shadow-sm shadow-indigo-950/5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-indigo-800/70">Dossiers enregistrés</div>
                            <div class="mt-2 text-3xl font-bold text-indigo-900">{{ $total }}</div>
                            <div class="mt-1 text-xs text-indigo-800/60">{{ $dossiersDuMois }} créés ce mois-ci</div>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                            <x-icon.folder class="h-5 w-5" />
                        </span>
                    </div>
                </div>

                <div class="rounded-xl border border-amber-100 bg-[linear-gradient(135deg,#fffbeb_0%,#ffffff_75%)] p-5 shadow-sm shadow-amber-950/5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-amber-700/80">En cours de traitement</div>
                            <div class="mt-2 text-3xl font-bold text-amber-600">{{ $enCours }}</div>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                    </div>
                    <div class="mt-3 h-1.5 w-full rounded-full bg-amber-100">
                        <div class="h-1.5 rounded-full bg-amber-500" style="width: {{ $partEnCours }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-amber-800/70">{{ $partEnCours }} % du volume total</div>
                </div>

                <div class="rounded-xl border border-orange-100 bg-[linear-gradient(135deg,#fff7ed_0%,#ffffff_75%)] p-5 shadow-sm shadow-orange-950/5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-orange-700/80">Dossiers incomplets</div>
                            <div class="mt-2 text-3xl font-bold text-orange-600">{{ $incomplets }}</div>
                            <div class="mt-1 text-xs text-orange-800/70">pièces manquantes à réclamer</div>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600 ring-1 ring-orange-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </span>
                    </div>
                </div>

                @if ($voitVisa)
                <div class="rounded-xl border border-teal-100 bg-[linear-gradient(135deg,#f0fdfa_0%,#ffffff_75%)] p-5 shadow-sm shadow-teal-950/5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-teal-700/80">Au pôle Visa</div>
                            <div class="mt-2 text-3xl font-bold text-teal-600">{{ $auVisa }}</div>
                            <div class="mt-1 text-xs text-teal-800/70">en attente de visa</div>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 ring-1 ring-teal-100">
                            <x-icon.check class="h-5 w-5" />
                        </span>
                    </div>
                    @if ($voitSolde)
                        <div class="mt-3 text-xs text-teal-800/70">au solde : {{ $auSolde }}</div>
                    @endif
                </div>
                @endif

                <div class="rounded-xl border border-violet-100 bg-[linear-gradient(135deg,#f5f3ff_0%,#ffffff_75%)] p-5 shadow-sm shadow-violet-950/5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-violet-700/80">Bordereaux reçus</div>
                            <div class="mt-2 text-3xl font-bold text-violet-700">{{ $bordereauxDuMois }}</div>
                            <div class="mt-1 text-xs text-violet-800/70">arrivées depuis le début du mois</div>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 ring-1 ring-violet-100">
                            <x-icon.download class="h-5 w-5" />
                        </span>
                    </div>
                </div>
            </div>

            @if ($enRetard > 0)
                <div class="rounded-md bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800 flex items-center justify-between gap-3">
                    <span><strong>{{ $enRetard }}</strong> dossier(s) reçu(s) ou en traitement en retard
                        @if ($voitVisa && $voitSolde)
                            (Visa : &gt; 72 h ; autres : &gt; 7 jours).
                        @elseif ($voitVisa)
                            (&gt; 72 h).
                        @else
                            (&gt; 7 jours).
                        @endif
                    </span>
                    <a href="{{ route('dossiers.index', ['statut' => 'en_traitement']) }}" class="font-semibold underline">Voir les dossiers</a>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Répartition par statut --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-1">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-800 uppercase tracking-wide">
                        <svg class="h-4 w-4 shrink-0 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m6 10V5m6 14v-7m6 7H2"/>
                        </svg>
                        Répartition par statut
                    </h3>
                    <ul class="mt-4 space-y-3">
                        @php
                            $palette = ['#6366f1', '#f59e0b', '#f97316', '#10b981', '#8b5cf6', '#ec4899', '#0ea5e9', '#14b8a6'];
                        @endphp
                        @foreach ($statuts as $statut)
                            @php
                                $valeur = $parStatut[$statut->value];
                                $pourcent = $total > 0 ? round(($valeur / $total) * 100) : 0;
                                $couleur = $palette[$loop->index % count($palette)];
                            @endphp
                            <li>
                                <a href="{{ route('dossiers.index', ['statut' => $statut->value]) }}" class="block group">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600 group-hover:text-indigo-600">{{ $statut->label() }}</span>
                                        <span class="font-semibold text-gray-900">{{ $valeur }}</span>
                                    </div>
                                    <div class="mt-1 h-2 w-full rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full opacity-80 group-hover:opacity-100 transition" style="width: {{ $pourcent }}%; background-color: {{ $couleur }}"></div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Derniers dossiers --}}
                <div class="bg-white shadow-sm sm:rounded-lg lg:col-span-2 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-800 uppercase tracking-wide">
                        <svg class="h-4 w-4 shrink-0 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12V10a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8Z"/>
                        </svg>
                        Derniers dossiers
                    </h3>
                        <a href="{{ route('dossiers.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800">
                            Tout voir
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m0 0-6-6m6 6-6 6"/></svg>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Agent</th>
                                    <th class="px-6 py-3">Type de dossier</th>
                                    <th class="px-6 py-3">Division</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <th class="px-6 py-3">Créé le</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($derniersDossiers as $dossier)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3">
                                            <a href="{{ route('dossiers.show', $dossier) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                                {{ $dossier->agent?->nomComplet() }}
                                            </a>
                                            <div class="text-xs text-gray-500">IM {{ $dossier->agent?->im }}</div>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600">{{ $dossier->typeDossier?->libelleComplet() }}</td>
                                        <td class="px-6 py-3 text-gray-600">{{ $dossier->division?->nom }}</td>
                                        <td class="px-6 py-3"><x-statut-badge :statut="$dossier->statut" /></td>
                                        <td class="px-6 py-3 text-gray-500">{{ $dossier->cree_le?->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun dossier enregistré pour le moment.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Derniers bordereaux reçus --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-800 uppercase tracking-wide">
                        <svg class="h-4 w-4 shrink-0 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 14v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v9m0 0 3.5-3.5M12 13l-3.5-3.5"/>
                        </svg>
                        Derniers bordereaux reçus
                    </h3>
                        <a href="{{ route('bordereaux.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800">
                            Tout voir
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m0 0-6-6m6 6-6 6"/></svg>
                        </a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($derniersBordereaux as $bordereau)
                            <li class="px-6 py-3 flex items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('bordereaux.show', $bordereau) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        Arrivée n° {{ $bordereau->num_arrivee }} — {{ $bordereau->reference }}
                                    </a>
                                    <div class="text-xs text-gray-500">{{ $bordereau->serviceOrigine?->nom }}</div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    {{ $bordereau->date_arrivee?->format('d/m/Y') }}
                                    <div class="text-gray-400">{{ $bordereau->dossiers_count }} dossier(s)</div>
                                </div>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-gray-400">Aucun bordereau reçu.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Bordereaux de création de l'année : division qui tient la matricule --}}
                @if (Auth::user()->peutGererBordereauxDeCreation())
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-800 uppercase tracking-wide">
                        <svg class="h-4 w-4 shrink-0 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h6.5L18 8v11.5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.5V8H18"/>
                        </svg>
                        Bordereaux de création {{ now()->year }}
                    </h3>
                        <a href="{{ route('bordereaux-creation.index') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800">
                            Tout voir
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m0 0-6-6m6 6-6 6"/></svg>
                        </a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($bordereauxCreation as $bordereau)
                            <li class="px-6 py-3 flex items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('bordereaux-creation.show', $bordereau) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                        N° {{ str_pad($bordereau->num_ordre, 4, '0', STR_PAD_LEFT) }} — chapitre {{ $bordereau->chapitre }}
                                    </a>
                                    <div class="text-xs text-gray-500">{{ $bordereau->destination?->nom }}</div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    {{ $bordereau->lieu?->sigle }} · {{ $bordereau->annee }}
                                    <div class="text-gray-400">{{ $bordereau->lignes_count }} ligne(s)</div>
                                </div>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-gray-400">Aucun bordereau de création cette année.</li>
                        @endforelse
                    </ul>
                </div>
                @endif

                {{-- Alertes : dossiers rejetés non pris en charge depuis plus de 2 jours --}}
                @if(isset($dossiersRejetesNonPris) && $dossiersRejetesNonPris->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-rose-100 bg-rose-50/40 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-rose-800 uppercase tracking-wide">
                            <svg class="h-4 w-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            Alerte - Dossiers rejetés non pris en charge (> 2 jours)
                        </h3>
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800">{{ $dossiersRejetesNonPris->count() }}</span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($dossiersRejetesNonPris as $dossier)
                            @php
                                $dateRef = $dossier->modifie_le ?? $dossier->cree_le;
                                $nbJours = $dateRef ? (int) $dateRef->diffInDays(now()) : 0;
                                $couleur = $nbJours >= 5 ? 'text-rose-700 bg-rose-50' : ($nbJours >= 3 ? 'text-orange-700 bg-orange-50' : 'text-amber-700 bg-amber-50');
                            @endphp
                            <li class="px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="text-sm font-medium text-gray-900 hover:text-rose-700">
                                        Dossier #{{ $dossier->id }} - {{ $dossier->agent?->nomComplet() }}
                                    </a>
                                    <div class="text-xs text-gray-500">
                                        {{ $dossier->typeDossier?->libelleComplet() }} · Division : {{ $dossier->division?->nom }}
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    <div class="text-rose-700 font-medium">Rejeté - Non pris en charge</div>
                                    <div class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $couleur }}">
                                        Décompte : {{ $nbJours }} jour(s)
                                    </div>
                                    <div class="mt-1">Depuis le {{ $dateRef?->format('d/m/Y') }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Alerte : dossiers rejetés ou incomplets > 72 h --}}
                @if(isset($dossiersRejetesIncomplets72h) && $dossiersRejetesIncomplets72h->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-orange-100 bg-orange-50/40 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-orange-800 uppercase tracking-wide">
                            <svg class="h-4 w-4 shrink-0 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            Alerte — Dossiers rejetés ou incomplets (&gt; 72 h)
                        </h3>
                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">{{ $dossiersRejetesIncomplets72h->count() }}</span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($dossiersRejetesIncomplets72h as $dossier)
                            @php
                                $dateRef = $dossier->modifie_le ?? $dossier->cree_le;
                                $heures = $dateRef ? (int) $dateRef->diffInHours(now()) : 0;
                                $jours = intdiv((int) $heures, 24);
                                $couleur = $heures >= 120 ? 'text-rose-700 bg-rose-50' : 'text-orange-700 bg-orange-50';
                                $libStatut = $dossier->statut === \App\Enums\StatutDossier::Rejete ? 'Rejeté' : 'Incomplet';
                            @endphp
                            <li class="px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="text-sm font-medium text-gray-900 hover:text-orange-700">
                                        Dossier #{{ $dossier->id }} - {{ $dossier->agent?->nomComplet() }}
                                    </a>
                                    <div class="text-xs text-gray-500">
                                        {{ $dossier->typeDossier?->libelleComplet() }} · Division : {{ $dossier->division?->nom }}
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    <div class="text-orange-700 font-medium">{{ $libStatut }}</div>
                                    <div class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $couleur }}">
                                        {{ $jours >= 1 ? $jours.' j '.($heures % 24).' h' : $heures.' h' }}
                                    </div>
                                    <div class="mt-1">Depuis le {{ $dateRef?->format('d/m/Y H:i') }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Alerte SLA Visa : dossiers Visa reçus/en traitement > 72 h --}}
                @if(isset($dossiersVisaEnRetard) && $dossiersVisaEnRetard->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/40 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-amber-800 uppercase tracking-wide">
                            <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Alerte Visa — Délai &gt; 72 h (reçus / en traitement)
                        </h3>
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">{{ $dossiersVisaEnRetard->count() }}</span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($dossiersVisaEnRetard as $dossier)
                            @php
                                $heures = $dossier->cree_le ? (int) $dossier->cree_le->diffInHours(now()) : 0;
                                $jours = intdiv((int) $heures, 24);
                                $couleur = $heures >= 120 ? 'text-rose-700 bg-rose-50' : 'text-amber-700 bg-amber-50';
                            @endphp
                            <li class="px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="text-sm font-medium text-gray-900 hover:text-amber-700">
                                        Dossier #{{ $dossier->id }} - {{ $dossier->agent?->nomComplet() }}
                                    </a>
                                    <div class="text-xs text-gray-500">
                                        {{ $dossier->typeDossier?->libelleComplet() }} · Division : {{ $dossier->division?->nom }} · <x-statut-badge :statut="$dossier->statut" />
                                    </div>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    <div class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $couleur }}">
                                        {{ $jours >= 1 ? $jours.' j '.($heures % 24).' h' : $heures.' h' }}
                                    </div>
                                    <div class="mt-1">Créé le {{ $dossier->cree_le?->format('d/m/Y H:i') }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
