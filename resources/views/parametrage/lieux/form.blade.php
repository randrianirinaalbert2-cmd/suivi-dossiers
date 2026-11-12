@php($creation = !$lieu->exists)

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouveau lieu' : 'Modifier le lieu' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">Le code identifie le lieu dans les bordereaux de création.</p>
            </div>
            <a href="{{ route('lieux.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('lieux.store') : route('lieux.update', $lieu) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <x-input-label for="code" value="Code (3 chiffres)" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                          :value="old('code', $lieu->code)" maxlength="3" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="nom" value="Nom du lieu" />
                            <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full"
                                          :value="old('nom', $lieu->nom)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('nom')" />
                        </div>
                        <div>
                            <x-input-label for="sigle" value="Sigle" />
                            <x-text-input id="sigle" name="sigle" type="text" class="mt-1 block w-full"
                                          :value="old('sigle', $lieu->sigle)" maxlength="3" required />
                            <x-input-error class="mt-2" :messages="$errors->get('sigle')" />
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
                        <a href="{{ route('lieux.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
