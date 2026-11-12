<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Dossier #{{ $dossier->id }}</div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $dossier->agent?->nomComplet() }}</h2>
                    <x-statut-badge :statut="$dossier->statut" />
                    <x-pole-badge :pole="$dossier->pole" />
                    <?php
                        $enRetardDossier = in_array($dossier->statut, [\App\Enums\StatutDossier::Recu, \App\Enums\StatutDossier::EnTraitement], true)
                            && $dossier->cree_le
                            && (($dossier->pole === \App\Enums\PoleDossier::Visa && $dossier->cree_le->lte(now()->subHours(72)))
                                || ($dossier->pole !== \App\Enums\PoleDossier::Visa && $dossier->cree_le->lte(now()->subDays(7))));
                    ?>
                    @if ($enRetardDossier)
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                            En retard{{ $dossier->pole === \App\Enums\PoleDossier::Visa ? ' (Visa > 72 h)' : '' }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $dossier->typeDossier?->libelleComplet() }} · division {{ $dossier->division?->nom }} · IM {{ $dossier->agent?->im }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (in_array($dossier->statut, [\App\Enums\StatutDossier::Rejete, \App\Enums\StatutDossier::Incomplet], true) && $dossier->estComplet())
                    <form method="POST" action="{{ route('dossiers.valider-pieces', $dossier) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg border-2 border-sky-500 bg-sky-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-sky-600/30 transition hover:bg-sky-700 hover:border-sky-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Valider (OK) — pièces complètes
                        </button>
                    </form>
                @endif
                <button type="button"
                        x-on:click="$dispatch('open-modal', 'prise-dossier')"
                        class="inline-flex items-center gap-2 rounded-lg border-2 border-emerald-500 bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-600/30 transition hover:bg-emerald-700 hover:border-emerald-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Prendre le dossier
                </button>
                <a href="{{ route('dossiers.index') }}"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Liste
                </a>
                <a href="{{ route('dossiers.edit', $dossier) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-6"
         x-data="piecesDossier(@js([
             'url' => route('dossiers.pieces', $dossier),
             'csrf' => csrf_token(),
             'recues' => $completude['recues'],
             'exigees' => $completude['exigees'],
         ]))">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Fiche du dossier --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Fiche du dossier</h3>
                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Agent</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->agent?->nomComplet() }} (IM {{ $dossier->agent?->im }})</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Type de dossier</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->typeDossier?->libelleComplet() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Bordereau d'entrée</dt>
                            <dd class="mt-1">
                                <a href="{{ route('bordereaux.show', $dossier->bordereauRecu) }}" class="text-indigo-600 hover:text-indigo-800">
                                    Arrivée n° {{ $dossier->bordereauRecu?->num_arrivee }} — {{ $dossier->bordereauRecu?->reference }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Service d'origine</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->bordereauRecu?->serviceOrigine?->nom }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Objet</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->bordereauRecu?->objet ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Responsable</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->responsable?->nom }} <span class="text-xs text-gray-400">({{ $dossier->responsable?->nomRole() }})</span></dd>
                        </div>
                        @php
                            $dernierePrise = $dossier->historiques
                                ->where('type_action', 'prise_en_charge')
                                ->sortByDesc('date_heure_action')
                                ->first();
                        @endphp
                        @if ($dernierePrise)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Dernière prise en charge</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $dernierePrise->observation ?: 'Prise en charge enregistrée.' }}
                                    <span class="text-gray-500">— {{ $dernierePrise->date_heure_action?->format('d/m/Y à H:i') }}</span>
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Division</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->division?->nom }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Pôle de traitement</dt>
                            <dd class="mt-1"><x-pole-badge :pole="$dossier->pole" /></dd>
                        </div>
                        @if ($dossier->utiliseMoisExploitation())
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-400">Mois d'exploitation</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $dossier->mois_exploitation ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $dossier->mois_exploitation)->format('m/Y') : '—' }}
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Passage par le visa</dt>
                            <dd class="mt-1 text-gray-900">
                                @if ($dossier->passe_par_visa)
                                    Oui
                                    @if ($dossier->date_visa)
                                        <span class="text-gray-500">(visé le {{ $dossier->date_visa->format('d/m/Y') }})</span>
                                    @endif
                                @else
                                    Non
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Créé le</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->cree_le?->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-400">Dernière mise à jour</dt>
                            <dd class="mt-1 text-gray-900">{{ $dossier->modifie_le?->format('d/m/Y à H:i') }}</dd>
                        </div>
                        @if ($dossier->observation)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase text-gray-400">Observation</dt>
                                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $dossier->observation }}</dd>
                            </div>
                        @endif
                    </dl>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <dt class="text-xs font-semibold uppercase text-gray-500">Références visa / acte visé</dt>
                        <dd class="mt-1 text-gray-900">
                            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm">
                                <span><span class="text-gray-500">Réf. acte :</span> {{ $dossier->reference_acte ?: '—' }}</span>
                                <span><span class="text-gray-500">Date acte :</span> {{ $dossier->date_acte?->format('d/m/Y') ?: '—' }}</span>
                                <span><span class="text-gray-500">N° visa :</span> {{ $dossier->num_visa ?: '—' }}</span>
                                <span><span class="text-gray-500">Date visa :</span> {{ $dossier->date_visa?->format('d/m/Y') ?: '—' }}</span>
                            </div>
                        </dd>
                    </div>
                </div>

                {{-- Avancement des pièces --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Pièces du dossier</h3>

                    @if ($completude['exigees'] === 0)
                        <p class="mt-4 text-sm text-gray-500">Aucune pièce n'est définie pour ce type de dossier.</p>
                    @else
                        <div class="mt-4 text-center rounded-lg px-4 py-6"
                             :class="complet ? 'bg-emerald-50' : 'bg-amber-50'">
                            <div class="text-4xl font-bold"
                                 :class="complet ? 'text-emerald-700' : 'text-amber-700'"
                                 x-text="taux + ' %'">{{ $completude['taux'] }} %</div>
                            <div class="mt-1 text-xs font-semibold uppercase"
                                 :class="complet ? 'text-emerald-600' : 'text-amber-600'">
                                <span x-text="recues">{{ $completude['recues'] }}</span> pièce(s) sur
                                <span x-text="exigees">{{ $completude['exigees'] }}</span>
                            </div>
                        </div>
                        <div class="mt-3 h-2 w-full rounded-full bg-gray-100">
                            <div class="h-2 rounded-full transition-all"
                                 :class="complet ? 'bg-emerald-500' : 'bg-amber-500'"
                                 :style="'width: ' + taux + '%'"
                                 style="width: {{ $completude['taux'] }}%"></div>
                        </div>
                        <p class="mt-2 text-xs"
                           :class="complet ? 'text-emerald-600' : 'text-amber-700'"
                           x-text="complet ? 'Dossier complet.' : manquantes + ' pièce(s) manquante(s).'">
                            @if ($completude['manquantes'] === 0)
                                Dossier complet.
                            @else
                                {{ $completude['manquantes'] }} pièce(s) manquante(s).
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Pièces exigées --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden lg:col-span-2">
                    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Réception des pièces exigées</h3>
                            <p class="mt-1 text-xs text-gray-500">Cochez la pièce lorsqu'elle est effectivement reçue.</p>
                        </div>
                        @if ($piecesExigees->isNotEmpty())
                            <button type="button"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    x-on:click="toutBasculer($root)"
                                    x-bind:disabled="enCoursTout">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <span x-text="tousCoches ? 'Tout décocher' : 'Tout cocher'">Tout cocher</span>
                            </button>
                        @endif
                    </div>

                    @if ($piecesExigees->isEmpty())
                        <p class="px-6 py-8 text-center text-gray-400">Aucune pièce exigée pour ce type de dossier.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 w-10">Reçu</th>
                                    <th class="px-6 py-3">Pièce</th>
                                    <th class="px-6 py-3">Exemplaires</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($piecesExigees as $piece)
                                    @php
                                        $recue = isset($piecesRecues[$piece->type_piece_id]);
                                    @endphp
                                    <tr class="{{ $recue ? 'bg-emerald-50/40' : '' }}">
                                        <td class="px-6 py-3">
                                            <input type="checkbox"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 disabled:opacity-50"
                                                   data-piece-case="{{ $piece->type_piece_id }}"
                                                   {{ $recue ? 'checked' : '' }}
                                                   x-bind:disabled="enCoursTout || estEnCours($el.dataset.pieceCase)"
                                                   x-on:change="basculer($el.dataset.pieceCase, $el)" />
                                        </td>
                                        <td class="px-6 py-3">
                                            <span data-piece-libelle class="{{ $recue ? 'text-gray-900 font-medium' : 'text-gray-600' }}">{{ $piece->typePiece?->libelle }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-500">{{ $piece->nb_exemplaires }} ex.</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="px-6 py-3 border-t border-gray-100 text-xs text-gray-500"
                             x-show="message" x-text="message"></div>
                    @endif
                </div>

                {{-- Changement de statut --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Évolution du statut</h3>

                    @if (! empty($suite))
                        <form method="POST" action="{{ route('dossiers.statut', $dossier) }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="statut" value="Nouveau statut" />
                                <select id="statut" name="statut" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @foreach ($suite as $statut)
                                        <option value="{{ $statut->value }}">{{ $statut->label() }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('statut')" />
                            </div>

                            {{-- Le mois d'exploitation ne concerne que le pôle Solde. --}}
                            @if ($dossier->utiliseMoisExploitation())
                                <div x-data="{ mois: @js(old('mois_exploitation', $dossier->mois_exploitation)) }">
                                    <x-input-label for="mois_exploitation" value="Mois d'exploitation" />
                                    <div class="mt-1 flex items-center gap-2">
                                        <x-text-input id="mois_exploitation" name="mois_exploitation" type="month" class="block w-full" x-model="mois" />
                                        <button type="button"
                                                @click="mois = new Date().toISOString().slice(0,7)"
                                                class="shrink-0 rounded-md border border-gray-300 bg-white px-2.5 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                            Ce mois-ci
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Choisissez le mois d’exploitation du dossier (peut être différent du mois d’arrivée).</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('mois_exploitation')" />
                                </div>
                            @endif

                            <div>
                                <x-input-label for="observation" value="Observation (facultative)" />
                                <textarea id="observation" name="observation" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                          placeholder="Motif du changement...">{{ old('observation') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('observation')" />
                            </div>

                            <x-primary-button>
                                <svg class="me-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                Valider le changement
                            </x-primary-button>
                        </form>

                        <div class="mt-4 text-xs text-gray-400">
                            Statut actuel : <strong>{{ $dossier->statut->label() }}</strong>
                        </div>
                    @elseif (! empty($suiteBloquee))
                        <div class="mt-4 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Le dossier n'est pas clôturé (statut : {{ $dossier->statut->label() }}), mais aucune
                            évolution ne vous est ouverte.
                            <ul class="mt-2 list-disc pl-5 space-y-1">
                                <li>La transmission est réservée au chef de division ;</li>
                                <li>L'archivage est réservé au responsable du dossier.</li>
                            </ul>
                        </div>
                    @else
                        <div class="mt-4 rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600">
                            Dossier clôturé ({{ $dossier->statut->label() }}). Aucune évolution possible.
                        </div>
                    @endif
                </div>
            </div>

            @if ($dossier->pole === \App\Enums\PoleDossier::Visa)
                {{-- Visa : contrôle puis transmission au pôle Solde --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Visa — transmission au pôle Solde</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ce dossier est au pôle <strong>Visa</strong>. Après vérification et contrôle des responsables,
                        visez-le pour le transmettre au pôle <strong>Solde</strong> (mandatement).
                    </p>

                    <form method="POST" action="{{ route('dossiers.transmettre-solde', $dossier) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @csrf
                        <div>
                            <x-input-label for="num_visa" value="N° de visa" />
                            <x-text-input id="num_visa" name="num_visa" type="text" class="mt-1 block w-full"
                                          :value="old('num_visa', $dossier->num_visa)" />
                            <x-input-error class="mt-2" :messages="$errors->get('num_visa')" />
                        </div>
                        <div>
                            <x-input-label for="date_visa" value="Date du visa" />
                            <x-text-input id="date_visa" name="date_visa" type="date" class="mt-1 block w-full"
                                          :value="old('date_visa', $dossier->date_visa?->format('Y-m-d') ?? now()->toDateString())" />
                            <x-input-error class="mt-2" :messages="$errors->get('date_visa')" />
                        </div>
                        <div>
                            <x-input-label for="reference_acte" value="Référence de l'acte" />
                            <x-text-input id="reference_acte" name="reference_acte" type="text" class="mt-1 block w-full"
                                          :value="old('reference_acte', $dossier->reference_acte)" />
                            <x-input-error class="mt-2" :messages="$errors->get('reference_acte')" />
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label for="observation_visa" value="Observation (facultative)" />
                            <textarea id="observation_visa" name="observation" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('observation')" />
                        </div>
                        <div class="sm:col-span-3">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5"/>
                                </svg>
                                Viser et transmettre au Solde
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Parcours du dossier --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Parcours du dossier</h3>
                    <p class="mt-1 text-xs text-gray-500">Après traitement/saisie par les responsables, le dossier passe au chef de bureau, puis au chef de division, puis au chef de service (hiérarchique) pour la validation finale.</p>
                </div>
                <div class="px-6 py-5">
                    @php
                        $actionsParcours = ['prise_en_charge','attribution_responsable','changement_statut','creation'];

                        $prisParResponsables = $dossier->historiques->filter(function($h) use ($actionsParcours){
                            $u = $h->utilisateur;
                            if (!$u) return false;
                            return in_array($u->role, [
                                \App\Enums\RoleUtilisateur::Responsable,
                                \App\Enums\RoleUtilisateur::Verificateur,
                                \App\Enums\RoleUtilisateur::ResponsableControle,
                                \App\Enums\RoleUtilisateur::Liquidateur,
                            ]) && in_array($h->type_action, $actionsParcours);
                        })->unique('utilisateur_id')->values();

                        $prisParChefBureau = $dossier->historiques->filter(function($h){
                            $u = $h->utilisateur;
                            if (!$u) return false;
                            return $u->role === \App\Enums\RoleUtilisateur::ChefBureau
                                && in_array($h->type_action, ['prise_en_charge','attribution_responsable','changement_statut']);
                        })->unique('utilisateur_id')->values();

                        $prisParChefDivision = $dossier->historiques->filter(function($h){
                            $u = $h->utilisateur;
                            if (!$u) return false;
                            return $u->role === \App\Enums\RoleUtilisateur::ChefDivision
                                && in_array($h->type_action, ['prise_en_charge','attribution_responsable','changement_statut']);
                        })->unique('utilisateur_id')->values();

                        $prisParChefHierarchique = $dossier->historiques->filter(function($h){
                            $u = $h->utilisateur;
                            if (!$u) return false;
                            return $u->role === \App\Enums\RoleUtilisateur::ChefHierarchique
                                && in_array($h->type_action, ['prise_en_charge','attribution_responsable','changement_statut']);
                        })->unique('utilisateur_id')->values();
                    @endphp

                    <ol class="space-y-4">
                        <li class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $prisParResponsables->isNotEmpty() ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }} text-xs font-bold">1</span>
                                <span class="mt-1 h-full w-px bg-gray-200"></span>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="text-sm font-semibold text-gray-900">Responsables / Vérificateurs (traitement ou saisie)</div>
                                <div class="mt-1 text-xs {{ $prisParResponsables->isNotEmpty() ? 'text-emerald-700' : 'text-gray-500' }}">
                                    {{ $prisParResponsables->isNotEmpty() ? 'Terminé' : 'En attente' }}
                                </div>
                                @if($prisParResponsables->isNotEmpty())
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        @foreach($prisParResponsables as $h)
                                            <li>• {{ $h->utilisateur->nom }} ({{ $h->utilisateur->role?->label() }}) — {{ $h->date_heure_action?->format('d/m/Y H:i') }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </li>

                        <li class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $prisParChefBureau->isNotEmpty() ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }} text-xs font-bold">2</span>
                                <span class="mt-1 h-full w-px bg-gray-200"></span>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="text-sm font-semibold text-gray-900">Chef de bureau</div>
                                <div class="mt-1 text-xs {{ $prisParChefBureau->isNotEmpty() ? 'text-emerald-700' : 'text-gray-500' }}">
                                    {{ $prisParChefBureau->isNotEmpty() ? 'Terminé' : 'En attente' }}
                                </div>
                                @if($prisParChefBureau->isNotEmpty())
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        @foreach($prisParChefBureau as $h)
                                            <li>• {{ $h->utilisateur->nom }} — {{ $h->date_heure_action?->format('d/m/Y H:i') }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </li>

                        <li class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $prisParChefDivision->isNotEmpty() ? 'bg-violet-100 text-violet-700' : 'bg-gray-100 text-gray-400' }} text-xs font-bold">3</span>
                                <span class="mt-1 h-full w-px bg-gray-200"></span>
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="text-sm font-semibold text-gray-900">Chef de division</div>
                                <div class="mt-1 text-xs {{ $prisParChefDivision->isNotEmpty() ? 'text-emerald-700' : 'text-gray-500' }}">
                                    {{ $prisParChefDivision->isNotEmpty() ? 'Terminé' : 'En attente' }}
                                </div>
                                @if($prisParChefDivision->isNotEmpty())
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        @foreach($prisParChefDivision as $h)
                                            <li>• {{ $h->utilisateur->nom }} — {{ $h->date_heure_action?->format('d/m/Y H:i') }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </li>

                        <li class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $prisParChefHierarchique->isNotEmpty() ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-400' }} text-xs font-bold">4</span>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-900">Chef de service (hiérarchique) — validation / vérification finale</div>
                                <div class="mt-1 text-xs {{ $prisParChefHierarchique->isNotEmpty() ? 'text-emerald-700' : 'text-gray-500' }}">
                                    {{ $prisParChefHierarchique->isNotEmpty() ? 'Terminé' : 'En attente' }}
                                </div>
                                @if($prisParChefHierarchique->isNotEmpty())
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        @foreach($prisParChefHierarchique as $h)
                                            <li>• {{ $h->utilisateur->nom }} — {{ $h->date_heure_action?->format('d/m/Y H:i') }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="mt-3 rounded-md border border-gray-100 bg-gray-50 px-3 py-2 text-xs">
                                    <span class="font-medium text-gray-700">État du dossier :</span>
                                    <span class="ms-1"><x-statut-badge :statut="$dossier->statut" /></span>
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>

            {{-- Documents numérisés --}}
            <x-documents :documents="$dossier->documents->sortByDesc('date_numerisation')->values()"
                         :route-stockage="route('documents.dossier.store', $dossier)"
                         titre="Pièces numérisées du dossier" />

            {{-- Historique --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Historique des actions</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse ($dossier->historiques as $evenement)
                        <li class="px-6 py-3 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $evenement->libelleAction() }}</div>
                                @if ($evenement->observation)
                                    <div class="text-xs text-gray-500">{{ $evenement->observation }}</div>
                                @endif
                                @if ($evenement->ancien_statut && $evenement->nouveau_statut && $evenement->ancien_statut !== $evenement->nouveau_statut)
                                    <div class="mt-1 text-xs">
                                        <x-statut-badge :statut="$evenement->ancien_statut" />
                                        <span class="mx-1 text-gray-400">→</span>
                                        <x-statut-badge :statut="$evenement->nouveau_statut" />
                                    </div>
                                @endif
                            </div>
                            <div class="text-right text-xs text-gray-500">
                                <div>{{ $evenement->utilisateur?->nom }}</div>
                                <div>{{ $evenement->date_heure_action?->format('d/m/Y à H:i') }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-gray-400">Aucune action enregistrée.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Prise en charge : enregistrement de l'identité --}}
    <x-modal name="prise-dossier"
             :show="$errors->has('pris_par_im') || $errors->has('pris_par_nom') || $errors->has('pris_par_prenom') || $errors->has('pris_par_role')"
             focusable>
        <form method="POST" action="{{ route('dossiers.prendre', $dossier) }}" class="p-6">
            @csrf

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Enregistrer la prise en charge</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Renseignez l'identité de la personne qui prend le dossier. Ces informations seront
                        enregistrées dans l'historique du dossier.
                    </p>
                </div>
                <button type="button"
                        x-on:click="$dispatch('close-modal', 'prise-dossier')"
                        class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="pris_par_im" value="IM" />
                    <x-text-input id="pris_par_im" name="pris_par_im" type="text" class="mt-1 block w-full"
                                  :value="old('pris_par_im')" required autofocus placeholder="Ex. 12345" />
                    <x-input-error class="mt-2" :messages="$errors->get('pris_par_im')" />
                </div>

                <div>
                    <x-input-label for="pris_par_role" value="Rôle" />
                    <select id="pris_par_role" name="pris_par_role" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach (\App\Enums\RoleUtilisateur::cases() as $rolePrise)
                            <option value="{{ $rolePrise->value }}"
                                @selected(old('pris_par_role', auth()->user()->role?->value) === $rolePrise->value)>
                                {{ $rolePrise->label() }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('pris_par_role')" />
                </div>

                <div>
                    <x-input-label for="pris_par_nom" value="Nom" />
                    <x-text-input id="pris_par_nom" name="pris_par_nom" type="text" class="mt-1 block w-full"
                                  :value="old('pris_par_nom', auth()->user()->nom)" required placeholder="Nom de famille" />
                    <x-input-error class="mt-2" :messages="$errors->get('pris_par_nom')" />
                </div>

                <div>
                    <x-input-label for="pris_par_prenom" value="Prénom" />
                    <x-text-input id="pris_par_prenom" name="pris_par_prenom" type="text" class="mt-1 block w-full"
                                  :value="old('pris_par_prenom')" required placeholder="Prénom(s)" />
                    <x-input-error class="mt-2" :messages="$errors->get('pris_par_prenom')" />
                </div>
            </div>

            <div class="mt-4 rounded-md bg-gray-50 px-4 py-3 text-xs text-gray-600">
                Division : <strong>{{ auth()->user()->division?->nom ?? '—' }}</strong>
                <span class="mx-1 text-gray-300">·</span>
                Date et heure : <strong>{{ now()->format('d/m/Y H:i') }}</strong>
                <span class="mx-1 text-gray-300">·</span>
                Compte : <strong>{{ auth()->user()->nom }}</strong>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'prise-dossier')">
                    Annuler
                </x-secondary-button>
                <x-primary-button>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Enregistrer la prise en charge
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
