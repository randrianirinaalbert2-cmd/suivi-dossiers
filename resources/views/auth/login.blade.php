<x-guest-layout>
    <div class="mb-6 flex items-center gap-4">
        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0ea5e9,#06b6d4)] text-white shadow-lg shadow-indigo-500/30">
            <x-icon.user class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Connexion</h1>
            <p class="mt-0.5 text-sm text-gray-500">Bienvenue ! Connectez-vous pour accéder au suivi des dossiers.</p>
        </div>
    </div>

    <!-- État de la session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Courriel -->
        <div>
            <x-input-label for="courriel" :value="__('Email')" />
            <x-text-input id="courriel" class="block mt-1.5 w-full" type="email" name="courriel" :value="old('courriel')" required autofocus autocomplete="username" placeholder="vous@exemple.com">
                <x-slot:icone><x-icon.mail /></x-slot:icone>
            </x-text-input>
            <x-input-error :messages="$errors->get('courriel')" class="mt-2" />
        </div>

        <!-- Mot de passe -->
        <div x-data="{ masque: true }">
            <div class="flex items-center justify-between">
                <x-input-label for="mot_de_passe" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500 hover:underline">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="mot_de_passe" class="block mt-1.5 w-full" type="password" name="mot_de_passe" required autocomplete="current-password" placeholder="••••••••" x-bind:type="masque ? 'password' : 'text'">
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

        <!-- Se souvenir de moi -->
        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            {{ __('Remember me') }}
        </label>

    <x-primary-button class="w-full justify-center py-2.5 text-sm">
        <x-icon.arrow-right class="h-4 w-4" />
        {{ __('Log in') }}
    </x-primary-button>
    </form>

    <!-- Création de compte non disponible -->
    <div class="mt-5 border-t border-indigo-100 pt-4 text-center">
        <p class="text-xs text-gray-400">Les comptes sont créés par l'administration du service.</p>
    </div>
</x-guest-layout>
