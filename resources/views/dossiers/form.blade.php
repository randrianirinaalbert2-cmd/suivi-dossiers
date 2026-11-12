@php
    $creation = !$dossier->exists;
    $bordereauChoisi = old('bordereau_recu_id', $dossier->bordereau_recu_id ?? ($bordereau?->id));
    $typeChoisi = old('type_dossier_id', $dossier->type_dossier_id);
    $agentChoisi = old('agent_id', $dossier->agent_id);
    $responsableChoisi = old('responsable_id', $dossier->responsable_id ?? Auth::id());
    $poleChoisi = old('pole', $dossier->pole?->value ?? 'solde');
    $visaChoisi = (bool) old('passe_par_visa', $dossier->passe_par_visa ?? false);
    $dateVisaChoisie = old('date_visa', $dossier->date_visa?->format('Y-m-d'));
    $dateActeChoisie = old('date_acte', $dossier->date_acte?->format('Y-m-d'));
    $numVisaChoisi = old('num_visa', $dossier->num_visa);
    $referenceActeChoisie = old('reference_acte', $dossier->reference_acte);
    $nouvelAgentInitial = (bool) (old('agent_im') || old('agent_nom'));
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $creation ? 'Nouveau dossier' : 'Modification du dossier #' . $dossier->id }}</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $creation ? 'Créer un dossier' : $dossier->agent?->nomComplet() }}
                </h2>
            </div>
            <a href="{{ $creation ? route('dossiers.index') : route('dossiers.show', $dossier) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Retour
            </a>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="gestionDossier(@js($bordereauChoisi), @js($typeChoisi), @js($nouvelAgentInitial))">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (collect($bordereaux)->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 sm:p-8">
                    <div class="mx-auto max-w-xl text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100">
                            <svg class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-800">Aucun bordereau reçu pour l'instant</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Un dossier ne peut pas être créé seul : il doit être rattaché à une arrivée
                            (un bordereau reçu). Suivez ces étapes :
                        </p>

                        <ol class="mt-6 space-y-3 text-left text-sm text-gray-700">
                            <li class="flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">1</span>
                                <span><strong>Enregistrer le bordereau reçu</strong> — le document qui accompagne l'arrivée (service d'origine, n° et date d'arrivée).</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">2</span>
                                <span><strong>Ajouter les dossiers</strong> que ce bordereau contient (agent, type de dossier, pôle).</span>
                            </li>
                            <li class="flex items-start gap-3 rounded-lg bg-gray-50 px-4 py-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">3</span>
                                <span><strong>Traiter chaque dossier</strong> — pièces exigées, numérisation, historique, jusqu'à l'archivage.</span>
                            </li>
                        </ol>

                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('bordereaux.create') }}"
                               class="inline-flex items-center gap-2 rounded-md bg-[linear-gradient(90deg,#0284c7,#06b6d4)] px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-500/20 hover:brightness-110">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                </svg>
                                Enregistrer un bordereau reçu
                            </a>
                            <a href="{{ route('bordereaux.index') }}"
                               class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700"><x-icon.eye class="h-4 w-4" />Voir les bordereaux</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form method="POST" action="{{ $creation ? route('dossiers.store') : route('dossiers.update', $dossier) }}">
                        @csrf
                        @if (! $creation) @method('PUT') @endif

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="bordereau_recu_id" value="Bordereau d'entrée" />
                                <select id="bordereau_recu_id" name="bordereau_recu_id" required
                                        x-model.number="bordereau" @change="bordereauChangee()"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">— Sélectionner —</option>
                                    @foreach ($bordereaux as $item)
                                        <option value="{{ $item['id'] }}" data-division="{{ $item['division_id'] }}"
                                            @selected($bordereauChoisi == $item['id'])>{{ $item['label'] }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('bordereau_recu_id')" />
                                <p class="mt-1 text-xs text-gray-400">La division du dossier est celle du bordereau choisi.</p>
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="agent_id" value="Agent" />
                                <select id="agent_id" name="agent_id" x-bind:required="! nouvelAgent" x-bind:disabled="nouvelAgent"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 disabled:bg-gray-100 disabled:text-gray-400">
                                    <option value="">— Sélectionner —</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}" @selected($agentChoisi == $agent->id)>{{ $agent->libelle() }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('agent_id')" />

                                <label for="nouvel_agent" class="mt-3 inline-flex items-center gap-2 text-sm text-gray-600">
                                    <input id="nouvel_agent" type="checkbox" x-model="nouvelAgent"
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200">
                                    L'agent n'est pas encore dans la liste
                                </label>

                                <div x-show="nouvelAgent" x-cloak class="mt-3 grid grid-cols-1 gap-3 rounded-md bg-gray-50 p-3 sm:grid-cols-3">
                                    <div>
                                        <x-input-label for="agent_im" value="IM" />
                                        <x-text-input id="agent_im" name="agent_im" type="text" maxlength="30" placeholder="382 515"
                                                      x-bind:required="nouvelAgent" x-bind:disabled="! nouvelAgent"
                                                      class="mt-1 block w-full" :value="old('agent_im')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('agent_im')" />
                                    </div>
                                    <div>
                                        <x-input-label for="agent_nom" value="Nom" />
                                        <x-text-input id="agent_nom" name="agent_nom" type="text" maxlength="100" placeholder="RAKOTO"
                                                      x-bind:required="nouvelAgent" x-bind:disabled="! nouvelAgent"
                                                      class="mt-1 block w-full" :value="old('agent_nom')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('agent_nom')" />
                                    </div>
                                    <div>
                                        <x-input-label for="agent_prenoms" value="Prénoms" />
                                        <x-text-input id="agent_prenoms" name="agent_prenoms" type="text" maxlength="150" placeholder="Jean"
                                                      x-bind:disabled="! nouvelAgent"
                                                      class="mt-1 block w-full" :value="old('agent_prenoms')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('agent_prenoms')" />
                                    </div>
                                </div>

                                <p class="mt-2 text-xs text-gray-400">
                                    Cochez la case si l'agent est absent de la liste : il sera ajouté au référentiel d'après son IM.
                                    Pour plusieurs agents sur un même bordereau, utilisez la « Saisie en masse ».
                                </p>
                            </div>

                            <div>
                                <x-input-label for="type_dossier_id" value="Type de dossier" />
                                <select id="type_dossier_id" name="type_dossier_id" required x-model.number="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">— Sélectionner —</option>
                                    @foreach ($typesDossiers as $typeDossier)
                                        <option value="{{ $typeDossier->id }}" data-division="{{ $typeDossier->division_id }}"
                                            :disabled="divisionChoisie !== null && {{ $typeDossier->division_id }} !== divisionChoisie"
                                            @selected($typeChoisi == $typeDossier->id)>
                                            {{ $typeDossier->libelleComplet() }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('type_dossier_id')" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="responsable_id" value="Responsable du traitement" />
                                <select id="responsable_id" name="responsable_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">— Moi-même —</option>
                                    @foreach ($responsables as $user)
                                        <option value="{{ $user->id }}" @selected($responsableChoisi == $user->id)>{{ $user->nom }} ({{ $user->nomRole() }})</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('responsable_id')" />
                            </div>

                            <div>
                                <x-input-label for="pole" value="Pôle de traitement" />
                                <select id="pole" name="pole"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @foreach ($poles as $pole)
                                        <option value="{{ $pole->value }}" @selected($poleChoisi === $pole->value)>{{ $pole->label() }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-400">Pôle qui prend actuellement le dossier en charge.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('pole')" />
                            </div>

                            <div class="sm:col-span-2 flex items-center gap-3 rounded-md bg-gray-50 px-3 py-2">
                                <input type="hidden" name="passe_par_visa" value="0">
                                <input id="passe_par_visa" name="passe_par_visa" type="checkbox" value="1" @checked($visaChoisi) class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200">
                                <x-input-label for="passe_par_visa" value="Ce dossier passe par le visa" class="!mb-0" />
                            </div>

                            <div>
                                <x-input-label for="date_visa" value="Date du visa" />
                                <x-text-input id="date_visa" name="date_visa" type="date" class="mt-1 block w-full" :value="$dateVisaChoisie" />
                                <p class="mt-1 text-xs text-gray-400">À renseigner lorsque le dossier a été visé.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('date_visa')" />
                            </div>

                            <div class="sm:col-span-2">
                                <div class="rounded-lg border border-gray-200 bg-white/80 p-4 shadow-sm">
                                    <h4 class="text-sm font-semibold text-gray-700">Références du visa / de l'acte</h4>
                                    <p class="mt-1 text-xs text-gray-400">À compléter si l'acte visé est connu (Visa ou Solde selon le circuit).</p>
                                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        <div class="sm:col-span-2">
                                            <x-input-label for="reference_acte" value="Référence de l'acte visé" />
                                            <x-text-input id="reference_acte" name="reference_acte" type="text" class="mt-1 block w-full" :value="$referenceActeChoisie" />
                                            <x-input-error class="mt-2" :messages="$errors->get('reference_acte')" />
                                        </div>
                                        <div>
                                            <x-input-label for="date_acte" value="Date de l'acte visé" />
                                            <x-text-input id="date_acte" name="date_acte" type="date" class="mt-1 block w-full" :value="$dateActeChoisie" />
                                            <x-input-error class="mt-2" :messages="$errors->get('date_acte')" />
                                        </div>
                                        <div>
                                            <x-input-label for="num_visa" value="N° de visa" />
                                            <x-text-input id="num_visa" name="num_visa" type="text" class="mt-1 block w-full" :value="$numVisaChoisi" />
                                            <x-input-error class="mt-2" :messages="$errors->get('num_visa')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>
                                <svg class="me-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                {{ $creation ? 'Créer le dossier' : 'Enregistrer' }}
                            </x-primary-button>
                            <a href="{{ $creation ? route('dossiers.index') : route('dossiers.show', $dossier) }}"
                               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <script>
        function gestionDossier(bordereauInitial, typeInitial, nouvelAgentInitial) {
            return {
                bordereau: bordereauInitial,
                type: typeInitial,
                nouvelAgent: Boolean(nouvelAgentInitial),
                get divisionChoisie() {
                    const select = document.getElementById('bordereau_recu_id');
                    const option = select ? select.options[select.selectedIndex] : null;
                    const division = option ? option.dataset.division : null;
                    return division ? Number(division) : null;
                },
                bordereauChangee() {
                    this.$nextTick(() => {
                        const select = document.getElementById('type_dossier_id');
                        if (!select) return;
                        const option = select.options[select.selectedIndex];
                        if (option && option.disabled) {
                            select.value = '';
                            this.type = null;
                        }
                    });
                },
            };
        }
    </script>
</x-app-layout>
