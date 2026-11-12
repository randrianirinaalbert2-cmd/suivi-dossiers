@php($creation = !$service->exists)

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouveau service d\'origine' : 'Modifier le service' }}
                </h2>
            </div>
            <a href="{{ route('services-origine.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('services-origine.store') : route('services-origine.update', $service) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div>
                        <x-input-label for="nom" value="Nom du service" />
                        <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full"
                                      :value="old('nom', $service->nom)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('nom')" />
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
                        <a href="{{ route('services-origine.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
