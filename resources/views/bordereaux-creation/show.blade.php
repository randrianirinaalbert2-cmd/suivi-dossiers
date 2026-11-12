<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bordereau de création</div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $bordereau->annee }}/{{ str_pad($bordereau->num_ordre, 4, '0', STR_PAD_LEFT) }}
                    </h2>
                    <span class="inline-flex rounded bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">
                        {{ $bordereau->division?->nom }}
                    </span>
                    @if ($bordereau->estArchive())
                        <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-200">
                            Archivé le {{ $bordereau->archive_le?->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="inline-flex rounded bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                            En cours
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">Chapitre {{ $bordereau->chapitre }} · Paquet {{ $bordereau->paquet ?: '—' }} · {{ $bordereau->lieu?->nom }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('bordereaux-creation.index') }}"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Liste
                </a>
                <a href="{{ route('bordereaux-creation.edit', $bordereau) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('bordereaux-creation.imprimer', $bordereau) }}" target="_blank"
                   title="Bordereau d'envoi (BE) à remplir et signer"
                   class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                    </svg>
                    Imprimer le BE
                </a>
                <a href="{{ route('bordereaux-creation.soit-transmis', $bordereau) }}" target="_blank"
                   title="Note « Soit transmis » qui accompagne le bordereau d'envoi"
                   class="inline-flex items-center gap-2 rounded-md bg-teal-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5"/>
                    </svg>
                    Soit transmis
                </a>
                @if ($bordereau->estArchive())
                    <form method="POST" action="{{ route('bordereaux-creation.desarchiver', $bordereau) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15m0-3-3-3m0 0-3 3m3-3V15"/>
                            </svg>
                            Désarchiver
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('bordereaux-creation.archiver', $bordereau) }}"
                          onsubmit="return confirm('Archiver ce bordereau de création ?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                            Archiver
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('bordereaux-creation.destroy', $bordereau) }}"
                      onsubmit="return confirm('Supprimer ce bordereau de création ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                        </svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-6">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Informations</h3>
                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Destination</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->destination?->nom }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Lieu d'édition</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->lieu?->libelleComplet() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Chapitre / Paquet</dt>
                            <dd class="mt-1 font-mono text-gray-900">{{ $bordereau->chapitre }} / {{ $bordereau->paquet ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Année / Numéro d'ordre</dt>
                            <dd class="mt-1 font-mono text-gray-900">{{ $bordereau->annee }} / {{ str_pad($bordereau->num_ordre, 4, '0', STR_PAD_LEFT) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Date d'édition</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->date_edition?->format('d/m/Y') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Saisi par</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->utilisateur?->nom }}</dd>
                        </div>
                        @if ($bordereau->observation)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Observation</dt>
                                <dd class="mt-1 text-gray-900">{{ $bordereau->observation }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Contenu</h3>
                    <div class="mt-4 text-center rounded-lg bg-indigo-50 px-4 py-6">
                        <div class="text-4xl font-bold text-indigo-700">{{ $bordereau->lignes->count() }}</div>
                        <div class="mt-1 text-xs font-semibold uppercase text-indigo-500">agent(s) transmis</div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Liste des agents transmis</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3 w-12">N°</th>
                            <th class="px-6 py-3">IM</th>
                            <th class="px-6 py-3">Agent</th>
                            <th class="px-6 py-3">Type de dossier</th>
                            <th class="px-6 py-3">Observation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($bordereau->lignes as $index => $ligne)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-6 py-3 font-mono font-semibold text-gray-900">{{ $ligne->imAgent() }}</td>
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $ligne->nomCompletAgent() }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $ligne->typeDossier?->libelleComplet() }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ $ligne->observation ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucune ligne.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
