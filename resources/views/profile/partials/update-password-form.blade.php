<section>
    <header class="flex items-start gap-3">
        <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
            <x-icon.key class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Mot de passe</h2>
            <p class="mt-1 text-sm text-gray-500">Choisissez un mot de passe long et difficile à deviner.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="modif_mot_de_passe_actuel" value="Mot de passe actuel" />
            <x-text-input id="modif_mot_de_passe_actuel" name="mot_de_passe_actuel" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('mot_de_passe_actuel')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="modif_mot_de_passe" value="Nouveau mot de passe" />
            <x-text-input id="modif_mot_de_passe" name="mot_de_passe" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('mot_de_passe')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="modif_mot_de_passe_confirmation" value="Confirmer le nouveau mot de passe" />
            <x-text-input id="modif_mot_de_passe_confirmation" name="mot_de_passe_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('mot_de_passe_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5">
            <x-primary-button>
                <x-icon.check class="h-4 w-4" />
                Enregistrer
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-emerald-600">Mot de passe mis à jour.</p>
            @endif
        </div>
    </form>
</section>
