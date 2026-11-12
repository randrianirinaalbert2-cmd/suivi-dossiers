<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Types de dossiers</h2>
                <p class="mt-1 text-sm text-gray-500">Code, libellé et division de chaque type de dossier.</p>
            </div>
            <a href="{{ route('types-dossiers.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <x-icon.plus class="h-4 w-4" />
                Nouveau type de dossier
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <form method="GET" action="{{ route('types-dossiers.index') }}" class="flex flex-wrap gap-2">
                <x-text-input name="q" type="search" class="block w-full sm:w-64" placeholder="Code ou libellé..." :value="$recherche" />
                <select name="division_id" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <option value="">Toutes les divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" @selected((int) $divisionId === $division->id)>{{ $division->nom }}</option>
                    @endforeach
                </select>
                <select name="famille" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <option value="">Toutes les familles</option>
                    @foreach ($familles as $famille)
                        <option value="{{ $famille->value }}" @selected($familleId === $famille->value)>{{ $famille->libelleComplet() }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">
                    <x-icon.search class="h-4 w-4" />
                    Filtrer
                </x-secondary-button>
                @if ($recherche !== '' || $divisionId || $familleId)
                    <a href="{{ route('types-dossiers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 self-center"><x-icon.refresh class="h-4 w-4" />Réinitialiser</a>
                @endif
            </form>

            <div class="bg-white/95 shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100/90 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-6 py-3">Division</th>
                            <th class="px-6 py-3">Code</th>
                            <th class="px-6 py-3">Libellé</th>
                            <th class="px-6 py-3">Famille</th>
                            <th class="px-6 py-3">Pièces exigées</th>
                            <th class="px-6 py-3">Dossiers</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($typesDossiers as $type)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">{{ $type->division?->nom }}</span>
                                </td>
                                <td class="px-6 py-4 font-mono font-semibold text-gray-900">{{ $type->code }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $type->libelle }}</td>
                                <td class="px-6 py-4">
                                    @if ($type->famille)
                                        <x-famille-badge :famille="$type->famille" />
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('pieces-requises.index', ['type_dossier_id' => $type->id]) }}" class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-900 font-medium">
                                        <x-icon.folder class="h-4 w-4" />
                                        {{ $type->pieces_requises_count }} pièce(s)
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $type->dossiers_count }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('types-dossiers.edit', $type) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-900"><x-icon.pencil class="h-4 w-4" />Modifier</a>
                                    <form action="{{ route('types-dossiers.destroy', $type) }}" method="POST" class="inline ms-3"
                                          onsubmit="return confirm('Supprimer ce type de dossier ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 font-medium text-rose-600 hover:text-rose-900"><x-icon.trash class="h-4 w-4" />Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Aucun type de dossier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-gray-100">{{ $typesDossiers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
