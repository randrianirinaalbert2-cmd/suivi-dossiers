<x-guest-layout>
    <div class="mb-7 flex items-center gap-4">
        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0ea5e9,#06b6d4)] text-white shadow-lg shadow-indigo-500/30">
            <x-icon.mail class="h-6 w-6" />
        </span>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Vérification du courriel</h1>
            <p class="mt-0.5 text-sm text-gray-500">Merci de votre inscription ! Vérifiez votre adresse courriel pour continuer.</p>
        </div>
    </div>

    <div class="mb-4 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 font-medium text-sm text-green-700">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button class="w-full justify-center py-3 text-sm">
                <x-icon.envelope class="h-4 w-4" />
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <x-icon.logout class="h-4 w-4" />
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
