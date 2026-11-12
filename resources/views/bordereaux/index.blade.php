<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bordereaux reçus</h2>
                <p class="mt-1 text-sm text-gray-500">Entrées : bordereaux transmis par les services d'origine.</p>
            </div>
            <a href="{{ route('bordereaux.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                </svg>
                Nouveau bordereau
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <form method="GET" action="{{ route('bordereaux.index') }}"
                  class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-7">
                <div class="lg:col-span-2">
                    <x-input-label for="q" value="Référence ou n° d'arrivée" />
                    <x-text-input id="q" name="q" type="search" class="mt-1 block w-full" :value="$filtres['q'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="service_origine_id" value="Service d'origine" />
                    <select id="service_origine_id" name="service_origine_id"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Tous</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected(($filtres['service_origine_id'] ?? null) == $service->id)>{{ $service->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="division_id" value="Division" />
                    <select id="division_id" name="division_id"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        <option value="">Toutes</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(($filtres['division_id'] ?? null) == $division->id)>{{ $division->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="du" value="Arrivée du" />
                    <x-text-input id="du" name="du" type="date" class="mt-1 block w-full" :value="$filtres['du'] ?? ''" />
                </div>
                <div>
                    <x-input-label for="au" value="au" />
                    <x-text-input id="au" name="au" type="date" class="mt-1 block w-full" :value="$filtres['au'] ?? ''" />
                </div>
                <div class="flex items-end">
                    <x-secondary-button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/>
                        </svg>
                        Filtrer
                    </x-secondary-button>
                </div>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">N° arrivée</th>
                            <th class="px-6 py-3">Référence</th>
                            <th class="px-6 py-3">Service d'origine</th>
                            <th class="px-6 py-3">Date bordereau</th>
                            <th class="px-6 py-3">Date arrivée</th>
                            <th class="px-6 py-3">Division</th>
                            <th class="px-6 py-3">Dossiers</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($bordereaux as $bordereau)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono font-semibold text-gray-900">{{ $bordereau->num_arrivee }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('bordereaux.show', $bordereau) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $bordereau->reference }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->serviceOrigine?->nom }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->date_bordereau?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->date_arrivee?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $bordereau->division?->nom }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $bordereau->dossiers_count }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('dossiers.create', ['bordereau_recu_id' => $bordereau->id]) }}"
                                       class="inline-flex items-center gap-1 font-medium text-emerald-600 hover:text-emerald-900">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                        </svg>
                                        Ajouter un dossier
                                    </a>
                                    <a href="{{ route('bordereaux.edit', $bordereau) }}"
                                       class="ms-3 inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                        </svg>
                                        Modifier
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">Aucun bordereau reçu pour ces critères.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-gray-100">{{ $bordereaux->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
