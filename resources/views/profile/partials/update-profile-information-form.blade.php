<section>
    <header class="flex items-start gap-3">
        <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
            <x-icon.pencil class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Informations personnelles</h2>
            <p class="mt-1 text-sm text-gray-500">Votre photo, votre nom et votre adresse e-mail.</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6"
          x-data="{
              apercu: null,
              supprimer: {{ old('supprimer_photo') ? 'true' : 'false' }},
              choisirFichier(evenement) {
                  const fichier = evenement.target.files[0];
                  this.apercu = fichier ? URL.createObjectURL(fichier) : null;
              },
          }">
        @csrf
        @method('patch')

        {{-- Photo de profil --}}
        <div class="flex flex-col gap-4 rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-4 sm:flex-row sm:items-center">
            <div class="relative h-20 w-20 shrink-0">
                @if ($user->photo)
                    <img x-show="! apercu && ! supprimer" x-cloak
                         src="{{ $user->urlPhoto() }}" alt="Photo de {{ $user->nom }}"
                         class="h-20 w-20 rounded-full object-cover ring-4 ring-white shadow">
                @endif

                <img x-show="apercu" x-cloak :src="apercu ?? ''" alt="Aperçu de la nouvelle photo"
                     class="h-20 w-20 rounded-full object-cover ring-4 ring-white shadow">

                <span x-show="apercu === null && ({{ $user->photo ? 'false' : 'true' }} || supprimer)" x-cloak
                      class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-[linear-gradient(135deg,#38bdf8,#22d3ee)] text-xl font-semibold text-white ring-4 ring-white shadow">
                    {{ $user->initiales() }}
                </span>

                <span class="pointer-events-none absolute -bottom-1 -right-1 inline-flex h-7 w-7 items-center justify-center rounded-full bg-cyan-600 text-white shadow ring-2 ring-white">
                    <x-icon.camera class="h-3.5 w-3.5" />
                </span>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900">Photo de profil</p>
                <p class="text-xs text-gray-500">JPG, PNG ou WEBP — 2 Mo maximum.</p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <label for="photo"
                           class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 transition hover:bg-gray-50 focus-within:ring-2 focus-within:ring-cyan-500">
                        <x-icon.camera class="h-4 w-4 text-gray-500" />
                        {{ $user->photo ? 'Changer la photo' : 'Ajouter une photo' }}
                    </label>
                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                           class="sr-only" @change="choisirFichier($event)">

                    @if ($user->photo)
                        <label for="supprimer_photo"
                               class="inline-flex cursor-pointer items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium shadow-sm ring-1 transition"
                               :class="supprimer
                                   ? 'bg-rose-50 text-rose-700 ring-rose-200'
                                   : 'bg-white text-gray-700 ring-gray-300 hover:bg-gray-50'">
                            <input id="supprimer_photo" name="supprimer_photo" type="checkbox" value="1"
                                   class="sr-only" x-model="supprimer">
                            <x-icon.trash class="h-4 w-4" />
                            <span x-text="supprimer ? 'La photo sera supprimée' : 'Supprimer la photo'">Supprimer la photo</span>
                        </label>
                    @endif
                </div>

                <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                <x-input-error class="mt-2" :messages="$errors->get('supprimer_photo')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="nom" value="Nom complet" />
                <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full" :value="old('nom', $user->nom)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('nom')" />
            </div>

            <div>
                <x-input-label for="courriel" value="Adresse e-mail" />
                <x-text-input id="courriel" name="courriel" type="email" class="mt-1 block w-full" :value="old('courriel', $user->courriel)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('courriel')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-amber-700">
                            Votre adresse e-mail n'est pas encore vérifiée.

                            <button form="send-verification" class="inline-flex items-center gap-1.5 rounded-md text-sm font-medium underline hover:text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                <x-icon.envelope class="h-4 w-4" />
                                Renvoyer l'e-mail de vérification.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-emerald-600">
                                Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4 border-t border-gray-100 pt-5">
            <x-primary-button>
                <x-icon.check class="h-4 w-4" />
                Enregistrer
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-emerald-600">Profil mis à jour.</p>
            @endif
        </div>
    </form>
</section>
