<section class="space-y-4">
    <header class="flex items-start gap-3">
        <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
            <x-icon.trash class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Supprimer le compte</h2>
            <p class="mt-1 text-sm text-gray-500">
                La suppression du compte est définitive : toutes vos données seront perdues.
            </p>
        </div>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <x-icon.trash class="h-4 w-4" />
        Supprimer mon compte
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <x-icon.trash class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Voulez-vous vraiment supprimer votre compte ?
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Cette action est irréversible. Saisissez votre mot de passe pour confirmer la suppression définitive de votre compte.
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="mot_de_passe" value="Mot de passe" class="sr-only" />

                <x-text-input
                    id="mot_de_passe"
                    name="mot_de_passe"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Mot de passe"
                />

                <x-input-error :messages="$errors->userDeletion->get('mot_de_passe')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    <x-icon.x-mark class="h-4 w-4" />
                    Annuler
                </x-secondary-button>

                <x-danger-button>
                    <x-icon.trash class="h-4 w-4" />
                    Supprimer définitivement
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
