<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Utilisateurs</h2>
                <p class="mt-1 text-sm text-gray-500">Comptes, rôles et division de rattachement.</p>
            </div>
            <a href="{{ route('utilisateurs.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <x-icon.plus class="h-4 w-4" />
                Nouvel utilisateur
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <form method="GET" action="{{ route('utilisateurs.index') }}" class="flex gap-2">
                <x-text-input name="q" type="search" class="block w-full sm:w-80" placeholder="Nom ou courriel..." :value="$recherche" />
                <x-secondary-button type="submit">
                    <x-icon.search class="h-4 w-4" />
                    Rechercher
                </x-secondary-button>
                @if ($recherche !== '')
                    <a href="{{ route('utilisateurs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 self-center"><x-icon.refresh class="h-4 w-4" />Réinitialiser</a>
                @endif
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Nom</th>
                            <th class="px-6 py-3">Courriel</th>
                            <th class="px-6 py-3">Rôle</th>
                            <th class="px-6 py-3">Division</th>
                            <th class="px-6 py-3">E-mail vérifié</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($utilisateurs as $utilisateur)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $utilisateur->nom }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $utilisateur->courriel }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">
                                        {{ $utilisateur->nomRole() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $utilisateur->division?->nom ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($utilisateur->courriel_verifie_le)
                                        <span class="text-emerald-600 text-xs font-semibold">Oui</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Non</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('utilisateurs.edit', $utilisateur) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-900"><x-icon.pencil class="h-4 w-4" />Modifier</a>
                                    @if ($utilisateur->id !== Auth::id())
                                        <form action="{{ route('utilisateurs.destroy', $utilisateur) }}" method="POST" class="inline ms-3"
                                              onsubmit="return confirm('Supprimer le compte de {{ $utilisateur->nom }} ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 font-medium text-rose-600 hover:text-rose-900"><x-icon.trash class="h-4 w-4" />Supprimer</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Aucun utilisateur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-gray-100">{{ $utilisateurs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
