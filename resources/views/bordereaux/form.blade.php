@php
    $creation = !$bordereau->exists;
    $objetParDefaut = \App\Http\Controllers\BordereauRecuController::objetParDefautPourDivision(
        old('division_id', $bordereau->division_id) ? (int) old('division_id', $bordereau->division_id) : null
    );
    $objetsParDivision = $divisions
        ->mapWithKeys(fn ($division) => [
            $division->id => \App\Http\Controllers\BordereauRecuController::objetParDefautPourDivision($division->id),
        ])
        ->filter()
        ->all();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Nouveau bordereau reçu' : 'Modifier le bordereau' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">Enregistrez l'arrivée, puis créez les dossiers qu'il contient.</p>
            </div>
            <a href="{{ route('bordereaux.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $creation ? route('bordereaux.store') : route('bordereaux.update', $bordereau) }}">
                    @csrf
                    @if (! $creation) @method('PUT') @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2"
                         x-data="{
                            divisionId: @js(old('division_id', $bordereau->division_id)),
                            objet: @js(old('objet', $bordereau->objet)),
                            ancienObjetParDefaut: @js($objetParDefaut),
                            objetsParDivision: @js($objetsParDivision),
                            objetsParDefaut: @js(\App\Http\Controllers\BordereauRecuController::OBJETS_PAR_DEFAUT),
                            get objetParDefaut() {
                                return this.objetsParDivision[this.divisionId] ?? '';
                            },
                            changerDivision() {
                                const defaut = this.objetParDefaut;
                                this.ancienObjetParDefaut = defaut;
                                if (! defaut) {
                                    return;
                                }
                                // L'objet suit toujours la division choisie : une
                                // division Visa ne peut jamais porter
                                // « POUR MANDATEMENT », et inversement.
                                this.objet = defaut;
                            },
                         }">
                        <div x-data="{ serviceChoisi: @js((string) old('service_origine_id', $bordereau->service_origine_id)) }">
                            <x-input-label for="service_origine_id" value="Service d'origine" />
                            <select id="service_origine_id" name="service_origine_id" x-model="serviceChoisi" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Sélectionner —</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->nom }}</option>
                                @endforeach
                                <option value="__autre__">Autre (nouveau service)…</option>
                            </select>
                            <div x-show="serviceChoisi === '__autre__'" x-cloak class="mt-2">
                                <x-text-input id="nouveau_service_origine" name="nouveau_service_origine" type="text"
                                              class="mt-1 block w-full"
                                              placeholder="Nom du nouveau service d'origine"
                                              :value="old('nouveau_service_origine')" />
                                <p class="mt-1 text-xs text-gray-400">Ce service sera enregistré et réutilisable pour les prochaines saisies.</p>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('service_origine_id')" />
                            <x-input-error class="mt-2" :messages="$errors->get('nouveau_service_origine')" />
                        </div>

                        <div>
                            <x-input-label for="division_id" value="Division" />
                            <select id="division_id" name="division_id" required x-model="divisionId" @change="changerDivision()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Sélectionner —</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(old('division_id', $bordereau->division_id) == $division->id)>{{ $division->nom }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('division_id')" />
                        </div>

                        <div>
                            <x-input-label for="lieu_id" value="Localité" />
                            <select id="lieu_id" name="lieu_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Non renseignée —</option>
                                @foreach ($lieux as $lieu)
                                    <option value="{{ $lieu->id }}" @selected(old('lieu_id', $bordereau->lieu_id) == $lieu->id)>
                                        {{ $lieu->code }} — {{ $lieu->nom }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Code de localité porté sur le bordereau (ex. 304 pour Ambositra).</p>
                            <x-input-error class="mt-2" :messages="$errors->get('lieu_id')" />
                        </div>

                        <div>
                            <x-input-label for="nombre_dossiers_attendus" value="Nombre de dossiers annoncés" />
                            <x-text-input id="nombre_dossiers_attendus" name="nombre_dossiers_attendus" type="number"
                                          class="mt-1 block w-full" min="0" max="9999"
                                          :value="old('nombre_dossiers_attendus', $bordereau->nombre_dossiers_attendus)" />
                            <p class="mt-1 text-xs text-gray-400">« NOMBRE 16 DOSSIERS » indiqué sur le papier. L'application vérifiera que le compte est juste.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('nombre_dossiers_attendus')" />
                        </div>

                        <div>
                            <x-input-label for="reference" value="Référence du bordereau" />
                            <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full"
                                          :value="old('reference', $bordereau->reference)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('reference')" />
                        </div>

                        <div>
                            <x-input-label for="objet" value="Objet" />
                            <x-text-input id="objet" name="objet" type="text" class="mt-1 block w-full" maxlength="200"
                                          x-model="objet"
                                          x-bind:readonly="objetParDefaut !== ''"
                                          x-bind:class="objetParDefaut !== '' ? 'bg-gray-50 text-gray-700 cursor-not-allowed' : ''"
                                          x-bind:placeholder="objetParDefaut || 'Sélectionnez une division'"
                                          :value="old('objet', $bordereau->objet)" />
                            <p class="mt-1 text-xs text-gray-400"
                               x-text="objetParDefaut ? 'Objet imposé par la division sélectionnée : ' + objetParDefaut + ' (non modifiable).' : 'Renseigné automatiquement selon la division : Solde → POUR MANDATEMENT, Visa → POUR VISA.'">Renseigné automatiquement selon la division : Solde → POUR MANDATEMENT, Visa → POUR VISA.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('objet')" />
                        </div>

                        <div>
                            <x-input-label for="date_bordereau" value="Date du bordereau" />
                            <x-text-input id="date_bordereau" name="date_bordereau" type="date" class="mt-1 block w-full"
                                          :value="old('date_bordereau', $bordereau->date_bordereau?->toDateString())" required />
                            <x-input-error class="mt-2" :messages="$errors->get('date_bordereau')" />
                        </div>

                        <div>
                            <x-input-label for="num_arrivee" value="Numéro d'arrivée" />
                            <x-text-input id="num_arrivee" name="num_arrivee" type="text" class="mt-1 block w-full"
                                          :value="old('num_arrivee', $bordereau->num_arrivee)" maxlength="10" required />
                            <x-input-error class="mt-2" :messages="$errors->get('num_arrivee')" />
                        </div>

                        <div>
                            <x-input-label for="date_arrivee" value="Date d'arrivée" />
                            <x-text-input id="date_arrivee" name="date_arrivee" type="date" class="mt-1 block w-full"
                                          :value="old('date_arrivee', $bordereau->date_arrivee?->toDateString())" required />
                            <x-input-error class="mt-2" :messages="$errors->get('date_arrivee')" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="observation" value="Observation" />
                            <textarea id="observation" name="observation" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('observation', $bordereau->observation) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('observation')" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>
                            <x-icon.check class="h-4 w-4" />
                            {{ $creation ? 'Enregistrer le bordereau' : 'Enregistrer' }}
                        </x-primary-button>
                        <a href="{{ route('bordereaux.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
