@php($creation = !$typeDossier->exists)

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouveau type de dossier' : 'Modifier le type de dossier' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">Le code doit être unique au sein de la division.</p>
            </div>
            <a href="{{ route('types-dossiers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('types-dossiers.store') : route('types-dossiers.update', $typeDossier) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="sm:col-span-3">
                            <x-input-label for="division_id" value="Division" />
                            <select id="division_id" name="division_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Sélectionner —</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        @selected(old('division_id', $typeDossier->division_id) == $division->id)>{{ $division->nom }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('division_id')" />
                        </div>

                        <div>
                            <x-input-label for="code" value="Code" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                          :value="old('code', $typeDossier->code)" maxlength="10" required />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="libelle" value="Libellé" />
                            <x-text-input id="libelle" name="libelle" type="text" class="mt-1 block w-full"
                                          :value="old('libelle', $typeDossier->libelle)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
                        </div>

                        <div>
                            <x-input-label for="famille" value="Famille (code mouvement)" />
                            <select id="famille" name="famille"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Non classé —</option>
                                @foreach ($familles as $famille)
                                    <option value="{{ $famille->value }}"
                                        @selected(old('famille', $typeDossier->famille?->value) === $famille->value)>{{ $famille->libelleComplet() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Familles I à VII de la liste des codes mouvements.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('famille')" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>
                            @if ($creation)
                                <x-icon.plus class="h-4 w-4" />
                            @else
                                <x-icon.check class="h-4 w-4" />
                            @endif
                            {{ $creation ? 'Créer' : 'Enregistrer' }}
                        </x-primary-button>
                        @if (! $creation)
                            <a href="{{ route('pieces-requises.index', ['type_dossier_id' => $typeDossier->id]) }}"
                               class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800"><x-icon.folder class="h-4 w-4" />Gérer les pièces exigées<x-icon.arrow-right class="h-4 w-4" /></a>
                        @endif
                        <a href="{{ route('types-dossiers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
