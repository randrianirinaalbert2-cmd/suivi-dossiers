@php($surArchives = ($filtres['statut'] ?? '') === 'archive')
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $surArchives ? 'Archivage' : 'Dossiers' }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $surArchives
                        ? 'Dossiers classés sans suite. Utilisez les filtres pour retrouver un dossier archivé.'
                        : 'Suivi individuel des dossiers reçus, de la réception à l\'archivage.' }}
                </p>
            </div>
            <a href="{{ route('dossiers.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                </svg>
                Nouveau dossier
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            {{-- Filtres de statut (masqués en mode Archivage : tout est déjà archivé) --}}
            @unless ($surArchives)
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8v6l-4 2v-8L3 5Z"/>
                    </svg>
                    Statut :
                </span>
                <a href="{{ route('dossiers.index', array_filter(['q' => $filtres['q'] ?? '', 'pole' => $filtres['pole'] ?? null, 'type_dossier_id' => $filtres['type_dossier_id'] ?? null, 'division_id' => $filtres['division_id'] ?? null])) }}"
                   title="Afficher tous les dossiers"
                   class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition duration-150 hover:-translate-y-0.5 hover:shadow-sm {{ empty($filtres['statut']) ? 'bg-gray-900 text-white ring-gray-900' : 'cursor-pointer bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-700 hover:ring-indigo-300' }}">
                    Tous ({{ array_sum($compteurs) }})
                </a>
                @foreach ($statuts as $statut)
                    <a href="{{ route('dossiers.index', array_filter(array_merge(array_diff_key($filtres, ['statut' => '']), ['statut' => $statut->value]))) }}"
                       title="Filtrer : {{ $statut->label() }}"
                       class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition duration-150 hover:-translate-y-0.5 hover:shadow-sm {{ ($filtres['statut'] ?? '') === $statut->value ? 'bg-gray-900 text-white ring-gray-900' : 'cursor-pointer bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-700 hover:ring-indigo-300' }}">
                        {{ $statut->label() }} ({{ $compteurs[$statut->value] }})
                    </a>
                @endforeach
            </div>
            @endunless

            {{-- Filtres de pôle --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Pôle :</span>
                <a href="{{ route('dossiers.index', array_filter(array_diff_key($filtres, ['pole' => '']))) }}"
                   title="Tous les pôles"
                   class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition duration-150 hover:-translate-y-0.5 hover:shadow-sm {{ empty($filtres['pole']) ? 'bg-gray-900 text-white ring-gray-900' : 'cursor-pointer bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-700 hover:ring-indigo-300' }}">
                    Tous
                </a>
                @foreach ($poles as $pole)
                    <a href="{{ route('dossiers.index', array_filter(array_merge($filtres, ['pole' => $pole->value]))) }}"
                       title="Filtrer : {{ $pole->label() }}"
                       class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition duration-150 hover:-translate-y-0.5 hover:shadow-sm {{ ($filtres['pole'] ?? '') === $pole->value ? 'bg-gray-900 text-white ring-gray-900' : 'cursor-pointer bg-white text-gray-600 ring-gray-200 hover:bg-indigo-50 hover:text-indigo-700 hover:ring-indigo-300' }}">
                        {{ $pole->label() }} ({{ $compteursPole[$pole->value] }})
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('dossiers.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4">
                @if (!Auth::user()->voitToutesLesDivisions())
                    <input type="hidden" name="division_id" value="{{ Auth::user()->division_id }}">
                @endif
                @if ($surArchives)
                    <input type="hidden" name="statut" value="archive">
                @endif
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[220px] flex-[2]">
                        <x-input-label for="q" value="Agent (IM, nom) ou bordereau" />
                        <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$filtres['q'] ?? ''" placeholder="Rechercher…" />
                    </div>
                    <div class="min-w-[180px] flex-1">
                        <x-input-label for="type_dossier_id" value="Type de dossier" />
                        <select id="type_dossier_id" name="type_dossier_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tous</option>
                            @foreach ($typesDossiers as $type)
                                <option value="{{ $type->id }}" @selected(($filtres['type_dossier_id'] ?? null) == $type->id)>
                                    {{ $type->code }} — {{ $type->libelle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if (Auth::user()->voitToutesLesDivisions())
                        <div class="min-w-[180px] flex-1">
                            <x-input-label for="division_id" value="Division" />
                            <select id="division_id" name="division_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Toutes</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(($filtres['division_id'] ?? null) == $division->id)>{{ $division->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="min-w-[170px] flex-none">
                        <x-input-label for="mois_arrivee" value="Mois d'arrivée" />
                        <x-text-input id="mois_arrivee" name="mois_arrivee" type="month" class="mt-1 block w-full" :value="$filtres['mois_arrivee'] ?? ''" />
                    </div>
                    @if ($voitMoisExploitation)
                        <div class="min-w-[170px] flex-none">
                            <x-input-label for="mois_exploitation" value="Mois d'exploitation" />
                            <x-text-input id="mois_exploitation" name="mois_exploitation" type="month" class="mt-1 block w-full" :value="$filtres['mois_exploitation'] ?? ''" />
                        </div>
                    @endif
                    <div class="flex shrink-0 items-center gap-2 pb-[1px]">
                        <x-secondary-button type="submit" class="inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/>
                            </svg>
                            Filtrer
                        </x-secondary-button>
                        @if (array_filter($filtres))
                            <a href="{{ $surArchives ? route('dossiers.index', ['statut' => 'archive']) : route('dossiers.index') }}"
                               class="inline-flex items-center gap-1 whitespace-nowrap text-sm text-gray-500 hover:text-gray-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M21.015 4.356v4.992m0 0h-4.992m4.993 0-3.182-3.182a8.25 8.25 0 0 0-13.803 3.7"/>
                                </svg>
                                Réinit.
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">N° / Agent</th>
                                <th class="px-6 py-3">Type de dossier</th>
                                <th class="px-6 py-3">Bordereau</th>
                                <th class="px-6 py-3">Division</th>
                                <th class="px-6 py-3">Pôle</th>
                                <th class="px-6 py-3">Responsable</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Reçu le</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($dossiers as $dossier)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <a href="{{ route('dossiers.show', $dossier) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                            {{ $dossier->agent?->nomComplet() }}
                                        </a>
                                        <div class="text-xs text-gray-500">IM {{ $dossier->agent?->im }} · #{{ $dossier->id }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">{{ $dossier->typeDossier?->libelleComplet() }}</td>
                                    <td class="px-6 py-3 text-gray-600">
                                        <a href="{{ route('bordereaux.show', $dossier->bordereauRecu) }}" class="hover:text-indigo-600">
                                            N° {{ $dossier->bordereauRecu?->num_arrivee }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $dossier->division?->nom }}</span>
                                    </td>
                                    <td class="px-6 py-3"><x-pole-badge :pole="$dossier->pole" /></td>
                                    <td class="px-6 py-3 text-gray-600">{{ $dossier->responsable?->nom }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <x-statut-badge :statut="$dossier->statut" />
                                            <?php
                                                $enRetardDossier = in_array($dossier->statut, [\App\Enums\StatutDossier::Recu, \App\Enums\StatutDossier::EnTraitement], true)
                                                    && $dossier->cree_le
                                                    && (($dossier->pole === \App\Enums\PoleDossier::Visa && $dossier->cree_le->lte(now()->subHours(72)))
                                                        || ($dossier->pole !== \App\Enums\PoleDossier::Visa && $dossier->cree_le->lte(now()->subDays(7))));
                                            ?>
                                            @if ($enRetardDossier)
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 ring-1 ring-inset ring-amber-200" title="Délai dépassé">En retard</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">{{ $dossier->cree_le?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 text-right">
                                        @if (in_array($dossier->statut, [\App\Enums\StatutDossier::Rejete, \App\Enums\StatutDossier::Incomplet], true) && $dossier->estComplet())
                                            <form method="POST" action="{{ route('dossiers.valider-pieces', $dossier) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                    </svg>
                                                    Valider (OK)
                                                </button>
                                            </form>
                                        @elseif (in_array($dossier->statut, [\App\Enums\StatutDossier::Rejete, \App\Enums\StatutDossier::Incomplet], true))
                                            <span class="text-xs text-gray-400" title="En attente de toutes les pièces requises">Pièces manquantes</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">Aucun dossier pour ces critères.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-gray-100">{{ $dossiers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
