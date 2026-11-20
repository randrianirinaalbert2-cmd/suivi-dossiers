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
            @if ($dossier->situationAConfirmer())
                @php
                    $situationConfirmee = $dossier->situationConfirmee();
                    $confirmation = $dossier->derniereConfirmationSituation();
                    $codeCmvt = $dossier->typeDossier?->code;
                @endphp
                <div class="bg-white shadow-sm sm:rounded-lg border-l-4 {{ $situationConfirmee ? 'border-emerald-500' : 'border-amber-500' }}"
                     x-data="{ modifier: false }">
                    <div class="p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wide {{ $situationConfirmee ? 'text-emerald-700' : 'text-amber-700' }}">
                                    Situation de l'agent — CMVT {{ $codeCmvt }}
                                </h3>
                                @if ($situationConfirmee)
                                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $dossier->resumeSituation() }}</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        Confirmée le {{ $confirmation->date_heure_action?->format('d/m/Y à H:i') }}
                                        par {{ $confirmation->utilisateur?->nom }} ({{ $confirmation->utilisateur?->nomRole() }})
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-amber-800">
                                        À confirmer avant tout traitement : c'est cette situation qui détermine les pièces exigées.
                                        Tant qu'elle n'est pas confirmée, la réception des pièces est bloquée.
                                    </p>
                                @endif
                            </div>
                            @if ($situationConfirmee)
                                <button type="button" x-on:click="modifier = !modifier"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    <span x-text="modifier ? 'Annuler' : 'Modifier la situation'">Modifier la situation</span>
                                </button>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">
                                    En attente de confirmation
                                </span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('dossiers.situation', $dossier) }}"
                              class="mt-4 space-y-4" x-show="{{ $situationConfirmee ? 'modifier' : 'true' }}" x-cloak>
                            @csrf

                            @if ($codeCmvt === '17')
                                <div class="flex items-center gap-3">
                                    <input type="hidden" name="interruption_contrat_plus_3_mois" value="0">
                                    <input id="situation_interruption" name="interruption_contrat_plus_3_mois" type="checkbox" value="1"
                                           @checked(old('interruption_contrat_plus_3_mois', $dossier->interruption_contrat_plus_3_mois))
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200">
                                    <x-input-label for="situation_interruption"
                                                   value="Interruption de contrat de plus de 3 mois" class="!mb-0" />
                                </div>
                                <p class="pl-7 text-xs text-gray-500">
                                    Si oui, l'« Attestation de non interruption de service » est exigée.
                                </p>
                            @else
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="situation_nombre_enfants" value="Nombre d'enfants" />
                                        <x-text-input id="situation_nombre_enfants" name="nombre_enfants" type="number" min="0" max="30"
                                                      class="mt-1 block w-full" required
                                                      :value="old('nombre_enfants', $dossier->nombre_enfants ?? 0)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('nombre_enfants')" />
                                    </div>
                                    <div>
                                        <x-input-label for="situation_familiale" value="Situation familiale" />
                                        <select id="situation_familiale" name="situation_familiale" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="">— Choisir —</option>
                                            <option value="marie" @selected(old('situation_familiale', $dossier->situation_familiale) === 'marie')>Marié(e)</option>
                                            <option value="celibataire" @selected(old('situation_familiale', $dossier->situation_familiale) === 'celibataire')>Célibataire</option>
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('situation_familiale')" />
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="hidden" name="enfant_moins_de_4_ans" value="0">
                                        <input id="situation_enfant_moins_4" name="enfant_moins_de_4_ans" type="checkbox" value="1"
                                               @checked(old('enfant_moins_de_4_ans', $dossier->enfant_moins_de_4_ans))
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200">
                                        <x-input-label for="situation_enfant_moins_4" value="Enfant de moins de 4 ans" class="!mb-0" />
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="hidden" name="enfant_deja_integre" value="0">
                                        <input id="situation_enfant_integre" name="enfant_deja_integre" type="checkbox" value="1"
                                               @checked(old('enfant_deja_integre', $dossier->enfant_deja_integre))
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200">
                                        <x-input-label for="situation_enfant_integre" value="Enfant déjà intégré (CNAPS)" class="!mb-0" />
                                    </div>
                                </div>
                                <ul class="list-disc space-y-0.5 pl-5 text-xs text-gray-500">
                                    <li>Un acte de mariage ou un certificat de célibat est exigé selon la situation.</li>
                                    <li>La copie des enfants et l'attestation de non paiement dépendent des enfants.</li>
                                    <li>Un enfant déjà intégré (CNAPS) dispense de l'attestation de non paiement.</li>
                                </ul>
                            @endif

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg border-2 border-indigo-500 bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/30 transition hover:bg-indigo-700 hover:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    <span x-text="modifier ? 'Enregistrer la modification' : 'Confirmer la situation'">Confirmer la situation</span>
                                </button>
                                <p class="text-xs text-gray-400">La confirmation est tracée dans l'historique (nom, rôle, date).</p>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

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
                        @if (in_array($dossier->typeDossier?->code, \App\Models\Dossier::CODES_AVEC_RAPPEL, true))
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-400">Rappel de solde</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if ($dossier->avecRappelEffectif())
                                        Oui <span class="text-gray-500">— acte formaté exigé en 4 exemplaires</span>
                                    @else
                                        Non
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($dossier->typeDossier?->code === '17')
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-400">Interruption de contrat</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if ($dossier->interruption_contrat_plus_3_mois)
                                        Plus de 3 mois <span class="text-gray-500">— attestation de non interruption exigée</span>
                                    @else
                                        Non (ou 3 mois au plus)
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($dossier->typeDossier?->code === '60')
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-400">Enfants</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ (int) $dossier->nombre_enfants }} enfant(s)
                                    @if ($dossier->enfant_moins_de_4_ans)
                                        <span class="text-gray-500">— dont un de moins de 4 ans</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-400">Situation familiale</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ match ($dossier->situation_familiale) {
                                        'marie' => 'Marié(e)',
                                        'celibataire' => 'Célibataire',
                                        default => 'Non renseignée',
                                    } }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-400">Enfant déjà intégré (CNAPS)</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $dossier->enfant_deja_integre ? 'Oui — attestation de non paiement non exigée' : 'Non' }}
                                </dd>
                            </div>
                        @endif
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
                                <dt class="text-xs font-semibold uppercase text-gray-400">
                                    {{ in_array($dossier->statut, [\App\Enums\StatutDossier::Rejete, \App\Enums\StatutDossier::Incomplet], true) ? 'Motif de rejet' : 'Observation' }}
                                </dt>
                                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $dossier->observation }}</dd>
                            </div>
                        @endif
                    </dl>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <dt class="text-xs font-semibold uppercase text-gray-500">
                            {{ $dossier->pole === \App\Enums\PoleDossier::Visa ? 'Références visa' : 'Références visa / acte visé' }}
                        </dt>
                        <dd class="mt-1 text-gray-900">
                            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm">
                                {{-- La référence de l'acte est une donnée du pôle Solde : le Visa ne produit pas d'acte formaté. --}}
                                @if ($dossier->pole !== \App\Enums\PoleDossier::Visa)
                                    <span><span class="text-gray-500">Réf. acte :</span> {{ $dossier->reference_acte ?: '—' }}</span>
                                @endif
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

                    @if ($dossier->situationAConfirmer() && ! $dossier->situationConfirmee())
                        <div class="mx-6 mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Liste provisoire : confirmez d'abord la situation de l'agent ci-dessus pour figer les pièces
                            réellement exigées. La réception des pièces est bloquée tant que ce n'est pas fait.
                        </div>
                    @endif

                    @if ($piecesExigees->isEmpty())
                        <p class="px-6 py-8 text-center text-gray-400">Aucune pièce exigée pour ce type de dossier.</p>
                    @else
                        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 text-sm min-w-[640px]">
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
                                            @if ($piece->note)
                                                <span class="mt-0.5 block text-xs text-gray-400">{{ $piece->note }}</span>
                                            @endif
                                            @if ($piece->condition)
                                                <span class="mt-0.5 block text-xs text-indigo-500">
                                                    Exigée si : {{ \App\Models\Dossier::libelleCondition($piece->condition) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-gray-500">{{ $piece->nb_exemplaires }} ex.</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>

                        <div class="px-6 py-3 border-t border-gray-100 text-xs text-gray-500"
                             x-show="message" x-text="message"></div>
                    @endif
                </div>

                {{-- Changement de statut --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Évolution du statut</h3>

                    @if ($dossier->situationAConfirmer() && ! $dossier->situationConfirmee())
                        <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            Le passage en traitement est bloqué tant que la situation de l'agent n'est pas confirmée.
                        </p>
                    @endif

                    @if (! empty($suite))
                        <form method="POST" action="{{ route('dossiers.statut', $dossier) }}" class="mt-4 space-y-4"
                              x-data="{ nouveauStatut: @js(old('statut')) }">
                            @csrf
                            <div>
                                <x-input-label for="statut" value="Nouveau statut" />
                                <select id="statut" name="statut" required x-model="nouveauStatut"
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

                            {{-- Le numéro délivré par le visa est obligatoire avant « Traité ». --}}
                            @if ($dossier->pole === \App\Enums\PoleDossier::Visa)
                                <div x-show="nouveauStatut === 'traite'" x-cloak>
                                    <x-input-label for="num_visa_traitement" value="N° de visa (obligatoire pour « Traité »)" />
                                    <x-text-input id="num_visa_traitement" name="num_visa" type="text"
                                                  class="mt-1 block w-full"
                                                  :value="old('num_visa', $dossier->num_visa)" />
                                    <p class="mt-1 text-xs text-gray-500">
                                        La date de fin de traitement sera enregistrée automatiquement aujourd'hui,
                                        date à laquelle le numéro est attribué.
                                    </p>
                                    <x-input-error class="mt-2" :messages="$errors->get('num_visa')" />
                                </div>
                            @endif

                            {{-- Rejet / incomplet : le motif est obligatoire et peut être précisé librement. --}}
                            <div id="bloc-motif-rejet" hidden
                                 class="space-y-4 rounded-lg border border-rose-200 bg-rose-50/60 p-4">
                                <div>
                                    <x-input-label for="motif_rejet" value="Motif de rejet (obligatoire)" />
                                    <select id="motif_rejet" name="motif_rejet"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">— Choisir un motif —</option>
                                        @foreach ($motifsRejet as $motif)
                                            <option value="{{ $motif->value }}" data-aide="{{ $motif->aide() }}" @selected(old('motif_rejet') === $motif->value)>{{ $motif->label() }}</option>
                                        @endforeach
                                    </select>
                                    <p id="aide-motif-rejet" class="mt-1 text-xs text-gray-500">Choisissez le motif qui correspond le mieux au rejet.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('motif_rejet')" />
                                </div>

                                <div>
                                    <x-input-label for="detail_motif" value="Précision (ex. numéro de compte incomplet)" />
                                    <textarea id="detail_motif" name="detail_motif" rows="2"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                              placeholder="Précisez ce qui est incomplet ou non conforme…">{{ old('detail_motif') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Facultatif, sauf pour « Autre motif » où la précision est obligatoire.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('detail_motif')" />
                                </div>

                                @if (! empty($piecesManquantes))
                                    <div id="liste-pieces-manquantes" hidden>
                                        <p class="text-xs font-semibold uppercase text-gray-500">Pièces actuellement manquantes</p>
                                        <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs text-gray-600">
                                            @foreach ($piecesManquantes as $pieceManquante)
                                                <li>{{ $pieceManquante }}</li>
                                            @endforeach
                                        </ul>
                                        <p class="mt-1 text-xs text-gray-400">Cette liste sera jointe automatiquement au motif enregistré.</p>
                                    </div>
                                @endif
                            </div>

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

                        {{-- Affichage du motif : JavaScript natif pour rester fiable sur mobile comme sur PC. --}}
                        <script>
                            (() => {
                                const form = document.querySelector('form[action*="/statut"]');
                                const statut = form?.querySelector('select[name="statut"]');
                                const motif = form?.querySelector('select[name="motif_rejet"]');
                                if (!statut || !motif) {
                                    return;
                                }

                                const bloc = form.querySelector('#bloc-motif-rejet');
                                const detail = form.querySelector('#detail_motif');
                                const aide = form.querySelector('#aide-motif-rejet');
                                const liste = form.querySelector('#liste-pieces-manquantes');

                                const mettreAJour = () => {
                                    const estRejet = statut.value === 'rejete' || statut.value === 'incomplet';

                                    bloc.hidden = !estRejet;
                                    bloc.style.display = estRejet ? '' : 'none';
                                    motif.required = estRejet;
                                    if (detail) {
                                        detail.required = estRejet && motif.value === 'autre';
                                    }

                                    const option = motif.selectedOptions[0];
                                    if (aide) {
                                        aide.textContent = option?.dataset.aide || 'Choisissez le motif qui correspond le mieux au rejet.';
                                    }

                                    if (liste) {
                                        liste.hidden = motif.value !== 'pieces_manquantes';
                                        liste.style.display = motif.value === 'pieces_manquantes' ? '' : 'none';
                                    }
                                };

                                statut.addEventListener('change', mettreAJour);
                                motif.addEventListener('change', mettreAJour);
                                mettreAJour();
                            })();
                        </script>

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

                    <form method="POST" action="{{ route('dossiers.transmettre-solde', $dossier) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @csrf
                        <div>
                            <x-input-label for="num_visa_affiche" value="N° de visa (enregistré)" />
                            <div id="num_visa_affiche"
                                 class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                {{ $dossier->num_visa ?: 'Non enregistré' }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Numéro reçu avant le statut « Traité » ; repris automatiquement ici.
                            </p>
                        </div>
                        <div>
                            <x-input-label for="date_visa_affiche" value="Date de fin de traitement" />
                            <div id="date_visa_affiche"
                                 class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                {{ $dossier->date_visa?->format('d/m/Y') ?: '—' }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Date à laquelle le numéro a été attribué.</p>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="observation_visa" value="Observation (facultative)" />
                            <textarea id="observation_visa" name="observation" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('observation')" />
                        </div>
                        <div class="sm:col-span-2">
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
                    @php
                        $piecesRecues = $dossier->historiques
                            ->where('type_action', 'ajout_piece')
                            ->sortBy('date_heure_action')
                            ->values();
                        $piecesAvecAgent = $dossier->piecesRecuesAvecAgent();

                        // L'historique reste trié du plus récent au plus ancien : la
                        // ligne unique des pièces prend la place de la réception
                        // la plus récente, sans repousser les actions récentes.
                        $timeline = [];
                        $lignePiecesAjoutee = false;
                        foreach ($dossier->historiques as $evenementTimeline) {
                            if ($evenementTimeline->type_action === 'ajout_piece') {
                                if (! $lignePiecesAjoutee && $piecesRecues->isNotEmpty()) {
                                    $timeline[] = ['type' => 'pieces'];
                                    $lignePiecesAjoutee = true;
                                }
                                continue;
                            }
                            $timeline[] = ['type' => 'action', 'evenement' => $evenementTimeline];
                        }
                    @endphp

                    @forelse ($timeline as $entree)
                        @if ($entree['type'] === 'pieces')
                            {{-- Une seule ligne pour toutes les pièces reçues, avec un seul bouton. --}}
                        <li class="px-6 py-3 flex flex-wrap items-center justify-between gap-3 bg-gray-50/60">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    Pièces réceptionnées
                                    <span class="ms-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        {{ $piecesRecues->count() }}
                                    </span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $piecesRecues->first()?->utilisateur?->nom ?: 'Utilisateur supprimé' }}
                                    · du {{ $piecesRecues->first()?->date_heure_action?->format('d/m/Y') }}
                                    au {{ $piecesRecues->last()?->date_heure_action?->format('d/m/Y') }}
                                </div>
                            </div>
                            <button type="button"
                                    x-on:click="$dispatch('open-modal', 'detail-pieces-recues')"
                                    class="inline-flex items-center gap-1 rounded font-semibold text-indigo-600 transition hover:text-indigo-800 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Voir les détails
                            </button>

                            <x-modal name="detail-pieces-recues" maxWidth="lg">
                                <div class="p-6">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">Pièces réceptionnées</h3>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Dossier #{{ $dossier->id }} · {{ $dossier->agent?->nomComplet() }}
                                            </p>
                                        </div>
                                        <button type="button"
                                                x-on:click="$dispatch('close-modal', 'detail-pieces-recues')"
                                                class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="mt-5">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Qui a pris les pièces reçues</dt>
                                        <dd class="mt-1">
                                            @include('dossiers.partials.pieces-recues-liste', ['piecesRecues' => $piecesAvecAgent])
                                        </dd>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button type="button"
                                                x-on:click="$dispatch('close-modal', 'detail-pieces-recues')"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            Fermer
                                        </button>
                                    </div>
                                </div>
                            </x-modal>
                        </li>
                        @else
                            @php
                                $evenement = $entree['evenement'];
                                $details = $evenement->detailsTracabilite();
                            @endphp
                        <li class="px-6 py-3 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $details['action'] }}</div>
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
                                <div>{{ $details['agent'] }}</div>
                                <div>{{ $evenement->date_heure_action?->format('d/m/Y à H:i') }}</div>
                                <button type="button"
                                        x-on:click="$dispatch('open-modal', 'detail-action-{{ $evenement->id }}')"
                                        class="mt-1 inline-flex items-center gap-1 rounded font-semibold text-indigo-600 transition hover:text-indigo-800 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75 9.75 6.75 9.75 6.75-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Voir les détails
                                </button>
                            </div>

                            {{-- Traçabilité complète de l'action --}}
                            <x-modal name="detail-action-{{ $evenement->id }}" maxWidth="lg">
                                <div class="p-6">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">Détails de l'action</h3>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Dossier #{{ $dossier->id }} · {{ $dossier->agent?->nomComplet() }}
                                            </p>
                                        </div>
                                        <button type="button"
                                                x-on:click="$dispatch('close-modal', 'detail-action-{{ $evenement->id }}')"
                                                class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Action</dt>
                                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $details['action'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Date et heure</dt>
                                            <dd class="mt-1 text-sm text-gray-700">{{ $details['date_heure'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">N° d'enregistrement</dt>
                                            <dd class="mt-1 text-sm text-gray-700">#{{ $details['identifiant'] }}</dd>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Agent</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $details['agent'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Rôle</dt>
                                            <dd class="mt-1 text-sm text-gray-700">{{ $details['role'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Division</dt>
                                            <dd class="mt-1 text-sm text-gray-700">{{ $details['division'] }}</dd>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Courriel</dt>
                                            <dd class="mt-1 text-sm text-gray-700">{{ $details['courriel'] }}</dd>
                                        </div>
                                        @if ($details['statut_avant'] || $details['statut_apres'])
                                            <div class="sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Statut</dt>
                                                <dd class="mt-1 text-sm text-gray-900">
                                                    {{ $details['statut_avant'] ?? '—' }}
                                                    <span class="mx-2 text-gray-400">→</span>
                                                    {{ $details['statut_apres'] ?? '—' }}
                                                </dd>
                                            </div>
                                        @endif
                                        @if ($details['num_visa'])
                                            <div>
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">N° venu du visa</dt>
                                                <dd class="mt-1 text-sm font-medium text-gray-900">{{ $details['num_visa'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Date de fin de traitement</dt>
                                                <dd class="mt-1 text-sm text-gray-700">{{ $details['date_fin_traitement'] ?: '—' }}</dd>
                                            </div>
                                        @endif
                                        @if (in_array($evenement->nouveau_statut, ['rejete', 'incomplet'], true))
                                            <div class="sm:col-span-2">
                                                <p class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
                                                    Dossier déclaré <strong>{{ $details['statut_apres'] }}</strong> : voici qui a pris les pièces reçues.
                                                </p>
                                            </div>
                                        @endif
                                        @if (! empty($details['pieces_prises']))
                                            <div class="sm:col-span-2">
                                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Qui a pris les pièces reçues</dt>
                                                <dd class="mt-1">
                                                    @include('dossiers.partials.pieces-recues-liste', ['piecesRecues' => $details['pieces_prises']])
                                                </dd>
                                            </div>
                                        @endif
                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Observation</dt>
                                            <dd class="mt-1 whitespace-pre-line rounded-lg bg-gray-50 p-3 text-sm text-gray-700">{{ $details['observation'] }}</dd>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">Code de l'action</dt>
                                            <dd class="mt-1 text-xs text-gray-400">{{ $details['type_action'] }}</dd>
                                        </div>
                                    </dl>

                                    <div class="mt-6 flex justify-end">
                                        <button type="button"
                                                x-on:click="$dispatch('close-modal', 'detail-action-{{ $evenement->id }}')"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            Fermer
                                        </button>
                                    </div>
                                </div>
                            </x-modal>
                        </li>
                        @endif
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
