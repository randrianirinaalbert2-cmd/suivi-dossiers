@php
    $creation = !$utilisateur->exists;
    $roleChoisi = old('role', $utilisateur->role?->value);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouvel utilisateur' : 'Modifier l\'utilisateur' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">Le rôle détermine l'étendue des droits et de la division.</p>
            </div>
            <a href="{{ route('utilisateurs.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('utilisateurs.store') : route('utilisateurs.update', $utilisateur) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="nom" value="Nom complet" />
                            <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full"
                                          :value="old('nom', $utilisateur->nom)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('nom')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="courriel" value="Adresse courriel" />
                            <x-text-input id="courriel" name="courriel" type="email" class="mt-1 block w-full"
                                          :value="old('courriel', $utilisateur->courriel)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('courriel')" />
                        </div>

                        <div>
                            <x-input-label for="role" value="Rôle" />
                            <select id="role" name="role" required x-data x-model="role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected($roleChoisi === $role->value)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('role')" />
                        </div>

                        <div>
                            <x-input-label for="division_id" value="Division" />
                            <select id="division_id" name="division_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Aucune (administrateur) —</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        @selected(old('division_id', $utilisateur->division_id) == $division->id)>{{ $division->nom }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('division_id')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="mot_de_passe" value="{{ $creation ? 'Mot de passe' : 'Nouveau mot de passe (laisser vide pour conserver)' }}" />
                            <x-text-input id="mot_de_passe" name="mot_de_passe" type="password" class="mt-1 block w-full"
                                          :required="$creation" placeholder="8 caractères minimum" />
                            <x-input-error class="mt-2" :messages="$errors->get('mot_de_passe')" />
                        </div>
                    </div>

                    <div class="mt-6 rounded-md bg-gray-50 px-4 py-3 text-xs text-gray-600" x-data>
                        <p class="font-semibold mb-1">Rôles :</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($roles as $role)
                                <li><strong>{{ $role->label() }}</strong> — {{ $role->description() }}</li>
                            @endforeach
                        </ul>
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
                        <a href="{{ route('utilisateurs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
