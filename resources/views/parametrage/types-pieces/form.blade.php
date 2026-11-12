@php($creation = !$typePiece->exists)

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouveau type de pièce' : 'Modifier le type de pièce' }}
                </h2>
            </div>
            <a href="{{ route('types-pieces.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('types-pieces.store') : route('types-pieces.update', $typePiece) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div>
                        <x-input-label for="libelle" value="Libellé de la pièce" />
                        <x-text-input id="libelle" name="libelle" type="text" class="mt-1 block w-full"
                                      :value="old('libelle', $typePiece->libelle)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
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
                        <a href="{{ route('types-pieces.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
