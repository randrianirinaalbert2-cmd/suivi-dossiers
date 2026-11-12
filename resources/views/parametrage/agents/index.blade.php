<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agents</h2>
                <p class="mt-1 text-sm text-gray-500">Identifiant matricule (IM), nom et prénoms des agents.</p>
            </div>
            <a href="{{ route('agents.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <x-icon.plus class="h-4 w-4" />
                Nouvel agent
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <form method="GET" action="{{ route('agents.index') }}" class="flex gap-2">
                <x-text-input name="q" type="search" class="block w-full sm:w-80" placeholder="Rechercher par IM, nom ou prénom..." :value="$recherche" />
                <x-secondary-button type="submit">
                    <x-icon.search class="h-4 w-4" />
                    Rechercher
                </x-secondary-button>
                @if ($recherche !== '')
                    <a href="{{ route('agents.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 self-center">
                        <x-icon.refresh class="h-4 w-4" />
                        Réinitialiser
                    </a>
                @endif
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">IM</th>
                            <th class="px-6 py-3">Nom</th>
                            <th class="px-6 py-3">Prénoms</th>
                            <th class="px-6 py-3">Dossiers</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($agents as $agent)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono font-semibold text-gray-900">{{ $agent->im }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $agent->nom }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $agent->prenoms ?: '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $agent->dossiers_count }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('agents.edit', $agent) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-900">
                                        <x-icon.pencil class="h-4 w-4" />
                                        Modifier
                                    </a>
                                    <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="inline ms-3"
                                          onsubmit="return confirm('Supprimer l\'agent {{ $agent->nomComplet() }} ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 font-medium text-rose-600 hover:text-rose-900">
                                            <x-icon.trash class="h-4 w-4" />
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun agent.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-gray-100">{{ $agents->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
