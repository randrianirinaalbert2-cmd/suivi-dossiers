<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bordereau reçu</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Arrivée n° {{ $bordereau->num_arrivee }} — {{ $bordereau->reference }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dossiers.create', ['bordereau_recu_id' => $bordereau->id]) }}"
                   class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Créer un dossier
                </a>
                @if (Auth::user()->peutGererBordereauxDeCreation() && $dossiers->contains(fn ($dossier) => $dossier->typeDossier?->division?->chargeLesMatricules()))
                    <a href="{{ route('bordereaux.generer-be', $bordereau) }}"
                       title="Reprendre automatiquement les agents de ce bordereau dans un bordereau de création"
                       class="inline-flex items-center gap-2 rounded-md bg-sky-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        Générer le BE
                    </a>
                    <a href="{{ route('bordereaux.soit-transmis', $bordereau) }}" target="_blank"
                       title="Note « Soit transmis » reprenant les dossiers traités de ce bordereau"
                       class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
                        </svg>
                        Soit transmis
                    </a>
                @endif
                @if ($bordereau->division?->poleNaturel() === \App\Enums\PoleDossier::Visa)
                    <a href="{{ route('bordereaux.soit-transmis-visa', $bordereau) }}" target="_blank"
                       title="Notes « Soit transmis » du Visa, une par type de dossier traité"
                       class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
                        </svg>
                        Soit transmis (Visa)
                    </a>
                @endif
                <a href="{{ route('bordereaux.edit', $bordereau) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('bordereaux.imprimer', $bordereau) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                    </svg>
                    Imprimer
                </a>
                <form method="POST" action="{{ route('bordereaux.destroy', $bordereau) }}"
                      onsubmit="return confirm('Supprimer ce bordereau ? Cette action est irréversible.');">
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
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Informations du bordereau</h3>
                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Service d'origine</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->serviceOrigine?->nom }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Division</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->division?->nom }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Localité</dt>
                            <dd class="mt-1 text-gray-900">
                                @if ($bordereau->lieu)
                                    {{ $bordereau->lieu->code }} — {{ $bordereau->lieu->nom }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Référence</dt>
                            <dd class="mt-1 font-mono text-gray-900">{{ $bordereau->reference }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Objet</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->objet ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Date du bordereau</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->date_bordereau?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Numéro d'arrivée</dt>
                            <dd class="mt-1 font-mono text-gray-900">{{ $bordereau->num_arrivee }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Date d'arrivée</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->date_arrivee?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Saisi par</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->utilisateur?->nom }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Observation</dt>
                            <dd class="mt-1 text-gray-900">{{ $bordereau->observation ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Synthèse</h3>
                    <div class="mt-4 text-center rounded-lg bg-indigo-50 px-4 py-6">
                        <div class="text-4xl font-bold text-indigo-700">{{ $dossiers->count() }}</div>
                        <div class="mt-1 text-xs font-semibold uppercase text-indigo-500">dossier(s) rattaché(s)</div>
                        @if ($bordereau->nombre_dossiers_attendus !== null)
                            <div class="mt-2 text-sm text-indigo-800">
                                sur <strong>{{ $bordereau->nombre_dossiers_attendus }}</strong> annoncé(s) sur le bordereau
                            </div>
                        @endif
                    </div>
                    @if (! $bordereau->nombreDossiersConforme())
                        <div class="mt-4 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            ⚠ Le bordereau papier annonce <strong>{{ $bordereau->nombre_dossiers_attendus }}</strong>
                            dossier(s), mais <strong>{{ $dossiers->count() }}</strong> seulement sont saisis.
                            Vérifiez qu'aucun dossier n'a été oublié.
                        </div>
                    @endif
                    <p class="mt-4 text-xs text-gray-500">
                        Chaque dossier est traité individuellement : pièces exigées, numérisation et historique.
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Dossiers du bordereau</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('dossiers.create-plusieurs', $bordereau) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                            Saisie en masse
                        </a>
                        <a href="{{ route('dossiers.create', ['bordereau_recu_id' => $bordereau->id]) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                            Ajouter un dossier
                        </a>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Agent</th>
                            <th class="px-6 py-3">Type de dossier</th>
                            <th class="px-6 py-3">Responsable</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Créé le</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($dossiers as $dossier)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $dossier->agent?->nomComplet() }}</a>
                                    <div class="text-xs text-gray-500">IM {{ $dossier->agent?->im }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ $dossier->typeDossier?->libelleComplet() }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $dossier->responsable?->nom }}</td>
                                <td class="px-6 py-3"><x-statut-badge :statut="$dossier->statut" /></td>
                                <td class="px-6 py-3 text-gray-500">{{ $dossier->cree_le?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun dossier rattaché à ce bordereau.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-documents :documents="$bordereau->documents->sortByDesc('date_numerisation')->values()"
                         :route-stockage="route('documents.bordereau.store', $bordereau)"
                         titre="Numérisation du bordereau" />
        </div>
    </div>
</x-app-layout>
