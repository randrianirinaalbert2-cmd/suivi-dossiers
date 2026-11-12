<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Destinations</h2>
                <p class="mt-1 text-sm text-gray-500">Organismes destinataires des bordereaux de création.</p>
            </div>
            <a href="{{ route('destinations.create') }}"
               class="inline-flex items-center gap-2 rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <x-icon.plus class="h-4 w-4" />
                Nouvelle destination
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Nom de la destination</th>
                            <th class="px-6 py-3">Bordereaux</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($destinations as $destination)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $destination->nom }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $destination->bordereaux_creation_count }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('destinations.edit', $destination) }}" class="inline-flex items-center gap-1.5 font-medium text-indigo-600 hover:text-indigo-900"><x-icon.pencil class="h-4 w-4" />Modifier</a>
                                    <form action="{{ route('destinations.destroy', $destination) }}" method="POST" class="inline ms-3"
                                          onsubmit="return confirm('Supprimer cette destination ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 font-medium text-rose-600 hover:text-rose-900"><x-icon.trash class="h-4 w-4" />Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucune destination.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-3 border-t border-gray-100">{{ $destinations->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
