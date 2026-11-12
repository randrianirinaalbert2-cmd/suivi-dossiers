@php($creation = !$agent->exists)

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouvel agent' : 'Modifier l\'agent' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">L'IM identifie de façon unique l'agent dans les dossiers et bordereaux.</p>
            </div>
            <a href="{{ route('agents.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('agents.store') : route('agents.update', $agent) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div x-data="{
                            im: @js(old('im', $agent->im)),
                            get majoration() {
                                const c = (this.im || '').trim().toUpperCase().charAt(0);
                                if (!c) return null;
                                if (c === '0') return { code: '0', libelle: 'HEE fonctionnaire permanent', classe: 'text-emerald-700' };
                                if (c === '1') return { code: '1', libelle: 'HEE non fonctionnaire permanent', classe: 'text-emerald-700' };
                                if (c >= 'A' && c <= 'J') return { code: 'A à J', libelle: 'HEE fonctionnaire non permanent', classe: 'text-sky-700' };
                                if (c === 'K' || c === 'L') return { code: 'K / L', libelle: 'Ambigü : fonctionnaire ou non fonctionnaire non permanent', classe: 'text-amber-700' };
                                if (c >= 'M' && c <= 'N') return { code: 'M / N', libelle: 'HEE non fonctionnaire non permanent', classe: 'text-sky-700' };
                                return { code: c, libelle: 'majoration IM non reconnue', classe: 'text-gray-500' };
                            }
                        }">
                            <x-input-label for="im" value="IM" />
                            <x-text-input id="im" name="im" type="text" class="mt-1 block w-full"
                                          x-model="im" :value="old('im', $agent->im)" maxlength="10" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('im')" />
                            <p class="mt-1 text-xs text-gray-400" x-show="majoration" x-cloak :class="majoration ? majoration.classe : ''">
                                Aide (avertissement, non bloquant) :
                                <span x-text="majoration ? 'majoration « ' + majoration.code + ' » — ' + majoration.libelle : ''"></span>
                            </p>
                        </div>
                        <div>
                            <x-input-label for="nom" value="Nom" />
                            <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full"
                                          :value="old('nom', $agent->nom)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('nom')" />
                        </div>
                        <div>
                            <x-input-label for="prenoms" value="Prénoms" />
                            <x-text-input id="prenoms" name="prenoms" type="text" class="mt-1 block w-full"
                                          :value="old('prenoms', $agent->prenoms)" />
                            <x-input-error class="mt-2" :messages="$errors->get('prenoms')" />
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
                        <a href="{{ route('agents.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
