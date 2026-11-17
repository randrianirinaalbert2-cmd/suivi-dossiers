@props([
    'name' => 'service_origine_id',
    'services',
    'value' => '',
    'inputId' => null,
    'placeholder' => 'Rechercher un service (code ou nom)…',
    'emptyLabel' => null,
    'allowAutre' => false,
    'autosubmit' => false,
])

@php
    $inputId ??= $name.'_recherche';
    $options = collect($services)
        ->map(fn ($service) => ['id' => (string) $service->id, 'label' => $service->nom])
        ->values()
        ->all();

    // Libellé affiché dès le rendu serveur (avant l'initialisation Alpine).
    $valeurChoisie = (string) $value;
    $labelChoisi = '';
    if ($valeurChoisie === '__autre__') {
        $labelChoisi = 'Autre (nouveau service)…';
    } elseif ($valeurChoisie !== '') {
        $labelChoisi = optional(
            collect($services)->first(fn ($service) => (string) $service->id === $valeurChoisie)
        )->nom ?? '';
    }
@endphp

<div class="relative"
     x-data="serviceCombobox(@js([
        'options' => $options,
        'initial' => (string) $value,
        'emptyLabel' => $emptyLabel,
        'allowAutre' => (bool) $allowAutre,
        'autosubmit' => (bool) $autosubmit,
     ]))"
     @keydown.escape="fermer()"
     @click.outside="fermer()">
    {{-- La valeur réellement envoyée au serveur. --}}
    <input type="hidden" name="{{ $name }}" :value="choisi">

    <div class="relative">
        <input id="{{ $inputId }}" type="text" x-model="recherche" value="{{ $labelChoisi }}"
               @focus="$event.target.select(); ouvert = true; actif = 0; saisie = false"
               @click="ouvert = true"
               @input="saisir($event)"
               @keydown.arrow-down.prevent="descendre()"
               @keydown.arrow-up.prevent="monter()"
               @keydown.enter.prevent="entrer()"
               @keydown.tab="ouvert = false"
               placeholder="{{ $placeholder }}"
               autocomplete="off" spellcheck="false"
               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        <button type="button" tabindex="-1" aria-label="Afficher la liste des services"
                @click="ouvert = ! ouvert"
                class="absolute inset-y-0 right-0 mt-1 flex items-center px-3 text-gray-400 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>
    </div>

    <div x-cloak :class="ouvert ? '' : 'hidden'"
         class="absolute z-30 mt-1 max-h-72 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-800">
        @if ($emptyLabel !== null)
            <button type="button" @click="choisirVide()"
                    class="block w-full px-3 py-2 text-left text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                {{ $emptyLabel }}
            </button>
        @endif

        <template x-for="(option, index) in filtres" :key="option.id">
            <button type="button" @click="choisir(option)"
                    class="block w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700"
                    :class="index === actif
                        ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-100'
                        : 'text-gray-700 dark:text-gray-200'">
                <span x-text="option.label"></span>
            </button>
        </template>

        <p x-cloak :class="filtres.length === 0 ? '' : 'hidden'" class="px-3 py-2 text-gray-400">
            Aucun service ne correspond à la recherche.
        </p>

        @if ($allowAutre)
            <button type="button" @click="choisirAutre()"
                    class="block w-full border-t border-gray-100 px-3 py-2 text-left font-medium text-indigo-600 hover:bg-indigo-50 dark:border-gray-700 dark:text-indigo-300 dark:hover:bg-gray-700">
                Autre (nouveau service)…
            </button>
        @endif
    </div>

    {{ $slot }}
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('serviceCombobox', (config = {}) => ({
                options: config.options ?? [],
                choisi: String(config.initial ?? ''),
                recherche: '',
                ouvert: false,
                actif: 0,
                saisie: false,
                racine: null,
                allowAutre: Boolean(config.allowAutre),
                autosubmit: Boolean(config.autosubmit),
                autreLabel: 'Autre (nouveau service)…',

                init() {
                    // $el dépend de la directive en cours d'évaluation (bouton
                    // d'une option, etc.) : on mémorise la racine une fois pour
                    // toutes pour retrouver le formulaire et le champ caché.
                    this.racine = this.$el;
                    this.recherche = this.choisi === '__autre__'
                        ? this.autreLabel
                        : this.labelDe(this.choisi);
                },

                normaliser(texte) {
                    return String(texte ?? '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase()
                        .trim();
                },

                labelDe(id) {
                    const option = this.options.find((item) => String(item.id) === String(id));

                    return option ? option.label : '';
                },

                get filtres() {
                    const texte = this.normaliser(this.recherche);
                    const labelChoisi = this.choisi && this.choisi !== '__autre__'
                        ? this.normaliser(this.labelDe(this.choisi))
                        : null;

                    // Champ au repos (sa valeur est le libellé choisi) : on montre tout.
                    if (texte === '' || texte === labelChoisi) {
                        return this.options;
                    }

                    return this.options.filter((option) => this.normaliser(option.label).includes(texte));
                },

                saisir(event) {
                    this.choisi = '';
                    this.saisie = true;
                    this.recherche = event.target.value;
                    this.ouvert = true;
                    this.actif = 0;
                },

                choisir(option) {
                    this.choisi = String(option.id);
                    this.saisie = false;
                    this.recherche = option.label;
                    this.ouvert = false;
                    this.soumettre();
                },

                choisirVide() {
                    this.choisi = '';
                    this.saisie = false;
                    this.recherche = '';
                    this.ouvert = false;
                    this.soumettre();
                },

                choisirAutre() {
                    this.choisi = '__autre__';
                    this.saisie = false;
                    this.recherche = this.autreLabel;
                    this.ouvert = false;
                },

                fermer() {
                    this.ouvert = false;

                    // Une recherche tapée mais non validée ne doit pas laisser
                    // croire que le service affiché est sélectionné.
                    if (! this.choisi && this.saisie) {
                        this.recherche = '';
                        this.saisie = false;
                    }
                },

                descendre() {
                    this.ouvert = true;
                    if (this.filtres.length > 0) {
                        this.actif = Math.min(this.actif + 1, this.filtres.length - 1);
                    }
                },

                monter() {
                    if (this.filtres.length > 0) {
                        this.actif = Math.max(this.actif - 1, 0);
                    }
                },

                entrer() {
                    if (! this.ouvert) {
                        this.ouvert = true;

                        return;
                    }

                    // Rien n'a été tapé : la sélection courante est conservée et
                    // la touche Entrée se comporte comme une validation du
                    // formulaire (sans choisir la première option de la liste).
                    if (! this.saisie) {
                        this.ouvert = false;
                        this.racine?.closest('form')?.requestSubmit();

                        return;
                    }

                    const option = this.filtres[this.actif]
                        ?? (this.filtres.length === 1 ? this.filtres[0] : null);

                    if (option) {
                        this.choisir(option);
                    }
                },

                soumettre() {
                    if (! this.autosubmit) {
                        return;
                    }

                    const formulaire = (this.racine ?? this.$el).closest('form');
                    if (! formulaire) {
                        return;
                    }

                    // La liaison :value est appliquée au prochain tick : on
                    // force la valeur du champ caché pour que le filtre parte
                    // avec l'identifiant qui vient d'être choisi.
                    const cache = (this.racine ?? this.$el).querySelector('input[type="hidden"]');
                    if (cache) {
                        cache.value = this.choisi;
                    }

                    formulaire.requestSubmit();
                },
            }));
        });
    </script>
@endonce
