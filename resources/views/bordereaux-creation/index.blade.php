<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bordereaux de création</h2>
                <p class="mt-1 text-sm text-gray-500">Sorties : bordereaux de transmission vers la destination (visa / solde).</p>
            </div>
            <a href="{{ route('bordereaux-creation.create') }}"
               class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                </svg>
                Nouveau bordereau de création
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <div class="flex items-center gap-1 border-b border-gray-200">
                @php($baseQuery = collect(request()->query())->except('page')->all())
                @foreach (['actifs' => ['En cours', $nbActifs], 'archives' => ['Archivés', $nbArchives], 'tous' => ['Tous', $nbActifs + $nbArchives]] as $cle => $onglet)
                    <a href="{{ route('bordereaux-creation.index', array_merge($baseQuery, ['statut' => $cle])) }}"
                       class="px-3 py-2 -mb-px text-sm font-medium border-b-2 {{ $statut === $cle ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $onglet[0] }}
                        <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $onglet[1] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('bordereaux-creation.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4">
                <input type="hidden" name="statut" value="{{ $statut }}">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[280px]">
                        <x-input-label for="q" value="Chapitre, paquet, n°, IM, nom ou prénom d'agent" />
                        <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$filtres['q'] ?? ''" />
                    </div>
                    <div class="w-full sm:w-auto">
                        <x-input-label for="annee" value="Année" />
                        <select id="annee" name="annee" class="mt-1 block w-full sm:w-32 rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">Toutes</option>
                            @foreach ($annees as $annee)
                                <option value="{{ $annee }}" @selected(($filtres['annee'] ?? null) == $annee)>{{ $annee }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <x-input-label for="lieu_id" value="Lieu d'édition" />
                        <select id="lieu_id" name="lieu_id" class="mt-1 block w-full sm:w-56 rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">Tous</option>
                            @foreach ($lieux as $lieu)
                                <option value="{{ $lieu->id }}" @selected(($filtres['lieu_id'] ?? null) == $lieu->id)>{{ $lieu->libelleComplet() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <x-input-label for="division_id" value="Division" />
                        <select id="division_id" name="division_id" class="mt-1 block w-full sm:w-44 rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">Toutes</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" @selected(($filtres['division_id'] ?? null) == $division->id)>{{ $division->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-secondary-button type="submit" class="inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/>
                            </svg>
                            Filtrer
                        </x-secondary-button>
                        <a href="{{ route('bordereaux-creation.index', ['statut' => $statut]) }}"
                           class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                            <x-icon.refresh class="h-4 w-4" />
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">N°</th>
                            <th class="px-6 py-3">Chapitre / Paquet</th>
                            <th class="px-6 py-3">Lieu d'édition</th>
                            <th class="px-6 py-3">Destination</th>
                            <th class="px-6 py-3">Date d'édition</th>
                            <th class="px-6 py-3">Lignes</th>
                            <th class="px-6 py-3">Division</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($bordereaux as $bordereau)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono font-semibold text-gray-900 whitespace-nowrap">
                                    <a href="{{ route('bordereaux-creation.show', $bordereau) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $bordereau->annee }}/{{ str_pad($bordereau->num_ordre, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 font-mono text-gray-700">{{ $bordereau->chapitre }} / {{ $bordereau->paquet ?: '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->lieu?->libelleComplet() }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->destination?->nom }}</td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $bordereau->date_edition?->format('d/m/Y') ?: '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->lignes_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $bordereau->division?->nom }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($bordereau->estArchive())
                                        <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-200">
                                            Archivé
                                        </span>
                                    @else
                                        <span class="inline-flex rounded bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                            En cours
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('bordereaux-creation.show', $bordereau) }}"
                                       class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-900"><x-icon.eye class="h-4 w-4" />Voir</a>
                                    <a href="{{ route('bordereaux-creation.imprimer', $bordereau) }}" target="_blank"
                                       class="ms-3 inline-flex items-center gap-1.5 font-medium text-gray-600 hover:text-gray-900"><x-icon.printer class="h-4 w-4" />Imprimer</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                                    Aucun bordereau de création pour ces critères.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-gray-100">{{ $bordereaux->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
