<x-guest-layout>
    <div class="mb-7 flex items-center gap-4">
        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0ea5e9,#06b6d4)] text-white shadow-lg shadow-indigo-500/30">
            <x-icon.lock class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Confirmation requise</h1>
            <p class="mt-0.5 text-sm text-gray-500">Zone sécurisée : confirmez votre mot de passe pour continuer.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <!-- Mot de passe -->
        <div x-data="{ masque: true }">
            <x-input-label for="mot_de_passe" :value="__('Password')" />
            <x-text-input id="mot_de_passe" class="block mt-1.5 w-full" type="password" name="mot_de_passe" required autocomplete="current-password" placeholder="Votre mot de passe" x-bind:type="masque ? 'password' : 'text'">
                <x-slot:icone><x-icon.lock /></x-slot:icone>
                <x-slot:action>
                    <button type="button" @click="masque = ! masque" class="text-gray-400 transition hover:text-indigo-500 focus:outline-none" :aria-label="masque ? 'Afficher le mot de passe' : 'Masquer le mot de passe'">
                        <svg x-show="masque" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="! masque" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.774 3.162 10.066 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243" />
                        </svg>
                    </button>
                </x-slot:action>
            </x-text-input>
            <x-input-error :messages="$errors->get('mot_de_passe')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center py-3 text-sm">
            <x-icon.check class="h-4 w-4" />
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
