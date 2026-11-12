@php
    $creation = ! $bordereau->exists;
    $sourceBordereau = $sourceBordereau ?? null;
    $typeSaisieManuelleId = $typesDossiers->firstWhere('code', '03')?->id;
    $lignesInitiales = collect(old('lignes', $bordereau->lignes->map(fn ($ligne) => [
        'agent_im' => $ligne->imAgent(),
        'agent_nom' => $ligne->nomAgent(),
        'agent_prenoms' => $ligne->prenomsAgent(),
        'type_dossier_id' => $ligne->type_dossier_id,
        'observation' => $ligne->observation,
    ])->all()));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $creation ? 'Nouveau bordereau' : 'Modification' }}</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Bordereau de création n° '.$bordereau->num_ordre : 'Bordereau '.$bordereau->annee.'/'.str_pad($bordereau->num_ordre, 4, '0', STR_PAD_LEFT) }}
                </h2>
            </div>
            <a href="{{ route('bordereaux-creation.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.arrow-left class="h-4 w-4" />Retour</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="gestionLignes(@js($lignesInitiales->values()))">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($sourceBordereau)
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                    <p class="font-semibold">
                        Lignes reprises du bordereau reçu n° {{ $sourceBordereau->num_arrivee }} — {{ $sourceBordereau->reference }}
                    </p>
                    <p class="mt-1">
                        {{ $lignesInitiales->count() }} dossier(s) pré-rempli(s). Vérifiez l'en-tête et complétez le lieu d'édition,
                        le chapitre, le paquet et la destination.
                    </p>
                    @if ($typeSaisieManuelleId)
                        <p class="mt-1 font-medium text-amber-800">
                            Les lignes <strong>CMVT 03 — Recrutement d'un EFA</strong> restent vides : saisissez l'IM et l'identité
                            de l'agent recruté (c'est ce dossier qui crée son matricule).
                        </p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ $creation ? route('bordereaux-creation.store') : route('bordereaux-creation.update', $bordereau) }}">
                @csrf
                @if (! $creation) @method('PUT') @endif

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">En-tête du bordereau</h3>

                    <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <x-input-label for="division_id" value="Division" />
                            <select id="division_id" name="division_id" required x-model.number="division" @change="divisionChangee()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Sélectionner —</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(old('division_id', $bordereau->division_id) == $division->id)>{{ $division->nom }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('division_id')" />
                        </div>

                        <div>
                            <x-input-label for="lieu_id" value="Lieu d'édition" />
                            <select id="lieu_id" name="lieu_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Sélectionner —</option>
                                @foreach ($lieux as $lieu)
                                    <option value="{{ $lieu->id }}" @selected(old('lieu_id', $bordereau->lieu_id) == $lieu->id)>{{ $lieu->code }} — {{ $lieu->nom }} ({{ $lieu->sigle }})</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('lieu_id')" />
                        </div>

                        <div>
                            <x-input-label for="destination_id" value="Destination" />
                            <select id="destination_id" name="destination_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Sélectionner —</option>
                                @foreach ($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected(old('destination_id', $bordereau->destination_id) == $destination->id)>{{ $destination->nom }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('destination_id')" />
                        </div>

                        <div>
                            <x-input-label for="chapitre" value="Chapitre" />
                            <x-text-input id="chapitre" name="chapitre" type="text" class="mt-1 block w-full" maxlength="6"
                                          :value="old('chapitre', $bordereau->chapitre)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('chapitre')" />
                        </div>

                        <div>
                            <x-input-label for="paquet" value="Paquet" />
                            <x-text-input id="paquet" name="paquet" type="text" class="mt-1 block w-full" maxlength="10"
                                          :value="old('paquet', $bordereau->paquet)" />
                            <x-input-error class="mt-2" :messages="$errors->get('paquet')" />
                        </div>

                        <div>
                            <x-input-label for="annee" value="Année" />
                            <x-text-input id="annee" name="annee" type="number" class="mt-1 block w-full"
                                          :value="old('annee', $bordereau->annee)" min="2000" max="2100" required />
                            <x-input-error class="mt-2" :messages="$errors->get('annee')" />
                        </div>

                        <div>
                            <x-input-label for="num_ordre" value="Numéro d'ordre" />
                            <x-text-input id="num_ordre" name="num_ordre" type="number" class="mt-1 block w-full"
                                          :value="old('num_ordre', $bordereau->num_ordre)" min="1" required />
                            <p class="mt-1 text-xs text-gray-400">Prochain numéro disponible attribué automatiquement à la création.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('num_ordre')" />
                        </div>

                        <div>
                            <x-input-label for="date_edition" value="Date d'édition" />
                            <x-text-input id="date_edition" name="date_edition" type="date" class="mt-1 block w-full"
                                          :value="old('date_edition', $bordereau->date_edition?->toDateString())" />
                            <x-input-error class="mt-2" :messages="$errors->get('date_edition')" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <x-input-label for="observation" value="Observation" />
                            <textarea id="observation" name="observation" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('observation', $bordereau->observation) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Lignes du bordereau : saisie manuelle de l'agent --}}
                <div class="mt-6 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Lignes (agents transmis)</h3>
                            <p class="mt-1 text-xs text-gray-500">Chaque ligne saisit les coordonnées de l'agent et le type de dossier transmis.</p>
                        </div>
                        <button type="button" @click="ajouter()"
                                class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            <x-icon.plus class="h-4 w-4" />
                            Ajouter une ligne
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 w-10">#</th>
                                    <th class="px-4 py-3 w-28">IM</th>
                                    <th class="px-4 py-3 min-w-40">Nom</th>
                                    <th class="px-4 py-3 min-w-40">Prénoms</th>
                                    <th class="px-4 py-3 min-w-56">Type de dossier</th>
                                    <th class="px-4 py-3 min-w-48">Observation</th>
                                    <th class="px-4 py-3 w-20"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(ligne, index) in lignes" :key="index">
                                    <tr class="hover:bg-gray-50 align-top"
                                        :class="Number(ligne.type_dossier_id) === {{ $typeSaisieManuelleId ?? 0 }} && ! ligne.agent_im ? 'bg-amber-50' : ''">
                                        <td class="px-4 py-3 text-gray-400" x-text="index + 1"></td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'lignes[' + index + '][agent_im]'" x-model="ligne.agent_im" maxlength="30"
                                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'lignes[' + index + '][agent_nom]'" x-model="ligne.agent_nom" maxlength="100"
                                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'lignes[' + index + '][agent_prenoms]'" x-model="ligne.agent_prenoms" maxlength="150"
                                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <select :name="'lignes[' + index + '][type_dossier_id]'" x-model="ligne.type_dossier_id"
                                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                <option value="">— Sélectionner —</option>
                                                @foreach ($typesDossiers as $type)
                                                    <option value="{{ $type->id }}" data-division="{{ $type->division_id }}"
                                                            x-bind:disabled="division && Number(division) !== {{ $type->division_id }}">{{ $type->libelleComplet() }}</option>
                                                @endforeach
                                            </select>
                                            @if ($typeSaisieManuelleId)
                                                <span x-show="Number(ligne.type_dossier_id) === {{ $typeSaisieManuelleId }}" x-cloak
                                                      class="mt-1 block text-xs font-semibold text-amber-700">
                                                    Agent à saisir manuellement
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" :name="'lignes[' + index + '][observation]'" x-model="ligne.observation" maxlength="255"
                                                   class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" @click="retirer(index)"
                                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-rose-600 hover:text-rose-900"><x-icon.trash class="h-4 w-4" />Retirer</button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="lignes.length === 0" x-cloak>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                        Aucune ligne. Cliquez sur « Ajouter une ligne ».
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if ($errors->has('lignes') || $errors->has('lignes.*'))
                        <div class="px-6 py-3 text-sm text-rose-600">
                            @foreach (array_merge($errors->get('lignes'), $errors->get('lignes.*')) as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <x-primary-button>
                        <x-icon.check class="h-4 w-4" />
                        {{ $creation ? 'Enregistrer le bordereau' : 'Enregistrer' }}
                    </x-primary-button>
                    <a href="{{ route('bordereaux-creation.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700"><x-icon.x-mark class="h-4 w-4" />Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function gestionLignes(initiales) {
            return {
                lignes: Array.isArray(initiales) ? initiales : [],
                division: Number(document.getElementById('division_id')?.value) || null,
                ajouter() {
                    this.lignes.push({ agent_im: '', agent_nom: '', agent_prenoms: '', type_dossier_id: '', observation: 'POUR COMPETENCE' });
                },
                retirer(index) {
                    this.lignes.splice(index, 1);
                },
                divisionChangee() {
                    this.$nextTick(() => {
                        document.querySelectorAll('select[name$="[type_dossier_id]"]').forEach(sel => {
                            const option = sel.selectedOptions[0];
                            if (option && option.disabled) {
                                sel.value = '';
                                sel.dispatchEvent(new Event('change'));
                            }
                        });
                    });
                },
            };
        }
    </script>
</x-app-layout>
