@props(['documents', 'routeStockage', 'titre' => 'Documents numérisés'])

<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-800 uppercase tracking-wide">
                        <svg class="h-4 w-4 shrink-0 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5M6.75 10.5h10.5M9 13.5h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h6.5L18 8v11.5a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z"/>
                        </svg>
                        {{ $titre }}
                    </h3>
        <span class="text-xs text-gray-400">{{ $documents->count() }} fichier(s)</span>
    </div>

    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <form method="POST" action="{{ $routeStockage }}" enctype="multipart/form-data"
              class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="grow min-w-56">
                <x-input-label for="fichier" value="Fichier (PDF, JPG, PNG, WEBP — 10 Mo max)" />
                <input id="fichier" name="fichier" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required
                       class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:me-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300 dark:hover:file:bg-indigo-900" />
                <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
            </div>
            <x-primary-button type="submit">
                <x-icon.check class="h-4 w-4" />
                Numériser et enregistrer
            </x-primary-button>
        </form>
    </div>

    <ul class="divide-y divide-gray-100">
        @forelse ($documents as $document)
            <li class="px-6 py-3 flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="truncate text-sm font-medium text-gray-900">{{ $document->nom_fichier }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $document->tailleLisible() }}
                        · numérisé le {{ $document->date_numerisation?->format('d/m/Y à H:i') }}
                        · par {{ $document->utilisateur?->nom }}
                        <br>
                        <span class="font-mono text-[11px] text-gray-400">SHA-256 : {{ substr($document->empreinte_fichier, 0, 32) }}…</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <a href="{{ route('documents.telecharger', $document) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-900">
                        <x-icon.download class="h-4 w-4" />
                        Télécharger
                    </a>
                    <form method="POST" action="{{ route('documents.destroy', $document) }}"
                          onsubmit="return confirm('Supprimer définitivement ce document ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 font-medium text-rose-600 hover:text-rose-900">
                            <x-icon.trash class="h-4 w-4" />
                            Supprimer
                        </button>
                    </form>
                </div>
            </li>
        @empty
            <li class="px-6 py-8 text-center text-gray-400">Aucun document numérisé.</li>
        @endforelse
    </ul>
</div>
