<x-guest-layout>
    <div class="mb-7 flex items-center gap-4">
        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0ea5e9,#06b6d4)] text-white shadow-lg shadow-indigo-500/30">
            <x-icon.mail class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Mot de passe oublié</h1>
            <p class="mt-0.5 text-sm text-gray-500">Indiquez votre courriel : nous vous enverrons un lien de réinitialisation.</p>
        </div>
    </div>

    <!-- État de la session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Courriel -->
        <div>
            <x-input-label for="courriel" :value="__('Email')" />
            <x-text-input id="courriel" class="block mt-1.5 w-full" type="email" name="courriel" :value="old('courriel')" required autofocus autocomplete="username" placeholder="vous@exemple.com">
                <x-slot:icone><x-icon.mail /></x-slot:icone>
            </x-text-input>
            <x-input-error :messages="$errors->get('courriel')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center py-3 text-sm">
            <x-icon.envelope class="h-4 w-4" />
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>

    <div class="mt-6 border-t border-indigo-100 pt-5 text-center text-sm text-gray-600">
        Vous vous souvenez de votre mot de passe ?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500 hover:underline">Se connecter</a>
    </div>
</x-guest-layout>
