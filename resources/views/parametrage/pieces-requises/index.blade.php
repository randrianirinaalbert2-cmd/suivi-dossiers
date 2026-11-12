<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pièces exigées</h2>
                <p class="mt-1 text-sm text-gray-500">Liste des pièces à fournir pour chaque type de dossier.</p>
            </div>
            <a href="{{ route('types-dossiers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Types de dossiers</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" action="{{ route('pieces-requises.index') }}" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-end gap-3"
                  x-data>
                <div class="grow">
                    <x-input-label for="type_dossier_id" value="Type de dossier" />
                    <select id="type_dossier_id" name="type_dossier_id" required @change="this.form.submit()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach ($typesDossiers as $type)
                            <option value="{{ $type->id }}" @selected($typeDossier->id === $type->id)>
                                {{ $type->division?->nom }} · {{ $type->code }} — {{ $type->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="button" onclick="window.location='{{ route('pieces-requises.index') }}'">
                    <x-icon.refresh class="h-4 w-4" />
                    Réinitialiser
                </x-secondary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">{{ $typeDossier->libelleComplet() }}</div>
                        <div class="text-xs text-gray-500">Division {{ $typeDossier->division?->nom }} — {{ $typeDossier->piecesRequises->count() }} pièce(s) exigée(s)</div>
                    </div>
                    <a href="{{ route('types-dossiers.edit', $typeDossier) }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800"><x-icon.pencil class="h-4 w-4" />Modifier le type</a>
                </div>

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Pièce</th>
                            <th class="px-6 py-3 w-56">Nombre d'exemplaires</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($typeDossier->piecesRequises as $piece)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $piece->typePiece?->libelle }}</td>
                                <td class="px-6 py-3">
                                    <form method="POST" action="{{ route('pieces-requises.update', $piece) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="nb_exemplaires" min="1" max="99" value="{{ old('nb_exemplaires', $piece->nb_exemplaires) }}"
                                               class="w-20 rounded-md border-gray-300 text-sm shadow-sm" />
                                        <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-900"><x-icon.check class="h-4 w-4" />Enregistrer</button>
                                    </form>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form method="POST" action="{{ route('pieces-requises.destroy', $piece) }}"
                                          onsubmit="return confirm('Retirer cette pièce des exigences ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium text-rose-600 hover:text-rose-900"><x-icon.trash class="h-4 w-4" />Retirer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucune pièce exigée pour ce type de dossier.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($typesPiecesDisponibles->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Ajouter une pièce exigée</h3>
                    <form method="POST" action="{{ route('pieces-requises.store') }}" class="mt-4 flex flex-wrap items-end gap-3">
                        @csrf
                        <input type="hidden" name="type_dossier_id" value="{{ $typeDossier->id }}">

                        <div class="grow min-w-64">
                            <x-input-label for="type_piece_id" value="Pièce" />
                            <select id="type_piece_id" name="type_piece_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @foreach ($typesPiecesDisponibles as $typePiece)
                                    <option value="{{ $typePiece->id }}">{{ $typePiece->libelle }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type_piece_id')" />
                        </div>

                        <div>
                            <x-input-label for="nb_exemplaires" value="Exemplaires" />
                            <input id="nb_exemplaires" type="number" name="nb_exemplaires" min="1" max="99" value="1"
                                   class="mt-1 w-28 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                            <x-input-error class="mt-2" :messages="$errors->get('nb_exemplaires')" />
                        </div>

                        <x-primary-button type="submit">
                            <x-icon.plus class="h-4 w-4" />
                            Ajouter
                        </x-primary-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
