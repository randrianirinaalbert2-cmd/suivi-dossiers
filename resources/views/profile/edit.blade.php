<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[linear-gradient(135deg,#0891b2,#4f46e5)] text-white shadow-md shadow-indigo-500/20">
                <x-icon.user class="h-5 w-5" />
            </span>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Mon profil</h2>
                <p class="text-sm text-gray-500">Votre identité et vos informations de connexion.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Carte de présentation --}}
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                <div class="relative h-32 overflow-hidden bg-[linear-gradient(120deg,#0e7490_0%,#0284c7_48%,#6366f1_100%)] sm:h-40">
                    <div class="absolute -right-12 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-24 left-8 h-52 w-52 rounded-full bg-white/10"></div>
                </div>

                <div class="px-4 pb-6 sm:px-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-5">
                            <x-avatar :utilisateur="$user"
                                      taille="h-24 w-24 sm:h-28 sm:w-28"
                                      texte="text-3xl"
                                      contour="-mt-12 shrink-0 ring-4 ring-white shadow-xl sm:-mt-14" />

                            <div class="pb-1">
                                <h1 class="text-2xl font-bold text-gray-900">{{ $user->nom }}</h1>

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700 ring-1 ring-indigo-100">
                                        <x-icon.user class="h-3.5 w-3.5" />
                                        {{ $user->nomRole() }}
                                    </span>

                                    @if ($user->division)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-2.5 py-1 font-medium text-cyan-700 ring-1 ring-cyan-100">
                                            <x-icon.folder class="h-3.5 w-3.5" />
                                            {{ $user->division->nom }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5 pb-1 text-sm text-gray-500 sm:items-end">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon.envelope class="h-4 w-4 text-gray-400" />
                                {{ $user->courriel }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon.calendar class="h-4 w-4 text-gray-400" />
                                Membre depuis {{ $user->cree_le?->format('d/m/Y') ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Statistiques --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 ring-1 ring-cyan-100">
                        <x-icon.folder class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-2xl font-bold leading-tight text-gray-900">{{ number_format($statistiques['dossiers'], 0, ',', ' ') }}</div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Dossiers suivis</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                        <x-icon.check class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-2xl font-bold leading-tight text-gray-900">{{ number_format($statistiques['archives'], 0, ',', ' ') }}</div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Dossiers archivés</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                        <x-icon.refresh class="h-5 w-5" />
                    </span>
                    <div>
                        <div class="text-2xl font-bold leading-tight text-gray-900">{{ number_format($statistiques['actions'], 0, ',', ' ') }}</div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Actions enregistrées</div>
                    </div>
                </div>
            </div>

            {{-- Informations, mot de passe et compte --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-7">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100 sm:p-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
