<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Saisie en masse</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Arrivée n° {{ $bordereau->num_arrivee }} — {{ $bordereau->reference }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $bordereau->serviceOrigine?->nom }}</p>
            </div>
            <a href="{{ route('bordereaux.show', $bordereau) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Retour au bordereau
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="saisieMultiple({{ $nbLignes }})">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($bordereau->nombre_dossiers_attendus !== null)
                <div class="rounded-md bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    Le bordereau papier annonce <strong>{{ $bordereau->nombre_dossiers_attendus }}</strong> dossier(s).
                    Vous pourrez en saisir autant que nécessaire ci-dessous.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">La saisie n'a pas pu être enregistrée :</p>
                    <ul class="mt-1 list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $erreur)
                            <li>{{ $erreur }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dossiers.en-masse', $bordereau) }}">
                @csrf

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="pole" value="Pôle de traitement" />
                            <select id="pole" name="pole"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @foreach ($poles as $poleOption)
                                    <option value="{{ $poleOption->value }}" @selected($poleParDefaut->value === $poleOption->value)>{{ $poleOption->label() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Appliqué à tous les dossiers de cette saisie.</p>
                        </div>

                        <div>
                            <x-input-label for="responsable_id" value="Responsable du traitement" />
                            <select id="responsable_id" name="responsable_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">— Moi-même —</option>
                                @foreach ($responsables as $user)
                                    <option value="{{ $user->id }}" @selected((int) old('responsable_id') === (int) $user->id)>{{ $user->nom }} ({{ $user->nomRole() }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-400">Appliqué à tous les dossiers de cette saisie.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Agents portés sur le bordereau</h3>
                        <button type="button" @click="ajouter()"
                                class="inline-flex items-center gap-1.5 rounded-md border border-transparent bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                            Ajouter une ligne
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-3 py-3 w-10">N°</th>
                                    <th class="px-3 py-3 w-40">IM</th>
                                    <th class="px-3 py-3">Nom</th>
                                    <th class="px-3 py-3">Prénoms</th>
                                    <th class="px-3 py-3 w-72">Type de dossier</th>
                                    <th class="px-3 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(ligne, index) in lignes" :key="index">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-center text-gray-400" x-text="index + 1"></td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="`dossiers[${index}][agent_im]`" x-model="ligne.agent_im" maxlength="30" required
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="382 515">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="`dossiers[${index}][agent_nom]`" x-model="ligne.agent_nom" maxlength="100" required
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="RAKOTO">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="`dossiers[${index}][agent_prenoms]`" x-model="ligne.agent_prenoms" maxlength="150"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Jean">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select :name="`dossiers[${index}][type_dossier_id]`" x-model="ligne.type_dossier_id" required
                                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="">— Sélectionner —</option>
                                                @foreach ($typesDossiers as $typeDossier)
                                                    <option value="{{ $typeDossier->id }}">{{ $typeDossier->libelleComplet() }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="retirer(index)" title="Supprimer cette ligne"
                                                    class="text-rose-500 hover:text-rose-700">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">
                            Un agent inconnu est automatiquement ajouté au référentiel d'après son IM.
                            Les espaces, points et tirets de l'IM sont ignorés. Un même agent et un même type de dossier
                            ne peuvent pas figurer deux fois.
                        </p>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600"><strong x-text="lignes.length"></strong> ligne(s)</span>
                            <a href="{{ route('bordereaux.show', $bordereau) }}"
                               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                                Annuler
                            </a>
                            <x-primary-button>
                                <svg class="me-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                Enregistrer les dossiers
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function saisieMultiple(nb) {
            const vide = () => ({ agent_im: '', agent_nom: '', agent_prenoms: '', type_dossier_id: '' });
            return {
                lignes: Array.from({ length: nb }, vide),
                ajouter() { this.lignes.push(vide()); },
                retirer(index) { this.lignes.splice(index, 1); if (!this.lignes.length) this.lignes.push(vide()); },
            };
        }
    </script>
</x-app-layout>
