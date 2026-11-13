<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Bordereau d'envoi</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sélection des dossiers — arrivée n° {{ $bordereau->num_arrivee }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $bordereau->reference }} · {{ $bordereau->lieu?->nom ?? '—' }}
                </p>
            </div>
            <a href="{{ route('bordereaux.show', $bordereau) }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon.arrow-left class="h-4 w-4" />Retour au bordereau
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bordereaux.preparer-be', $bordereau) }}">
                @csrf

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Chapitre du bordereau</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Tous les dossiers sélectionnés doivent appartenir au <strong>même chapitre</strong>.
                        Si le bordereau reçu contient d'autres chapitres, il faudra créer un autre BE pour chacun.
                    </p>

                    <div class="mt-4 max-w-xs">
                        <x-input-label for="chapitre" value="Chapitre" />
                        <x-text-input id="chapitre" name="chapitre" type="text" class="mt-1 block w-full"
                                      maxlength="6" :value="old('chapitre')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('chapitre')" />
                    </div>
                </div>

                <div class="mt-6 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Dossiers traités</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $dossiers->count() }} dossier(s) au statut « Traité » ou au-delà.
                                Cochez ceux du chapitre saisi ci-dessus.
                            </p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox"
                                   onclick="document.querySelectorAll('.case-dossier').forEach(c => c.checked = this.checked)"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            Tout sélectionner
                        </label>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 w-10"></th>
                                    <th class="px-4 py-3 w-28">IM</th>
                                    <th class="px-4 py-3">Nom</th>
                                    <th class="px-4 py-3">Prénoms</th>
                                    <th class="px-4 py-3">Type de dossier</th>
                                    <th class="px-4 py-3 w-36">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($dossiers as $dossier)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="dossiers[]" value="{{ $dossier->id }}"
                                                   class="case-dossier rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                   @checked(in_array($dossier->id, old('dossiers', [])))>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-gray-700">{{ $dossier->agent?->im ?? '—' }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $dossier->agent?->nom ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $dossier->agent?->prenoms ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $dossier->typeDossier?->libelleComplet() ?? '—' }}</td>
                                        <td class="px-4 py-3"><x-statut-badge :statut="$dossier->statut" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <x-primary-button>
                        <x-icon.check class="h-4 w-4" />
                        Continuer vers le bordereau
                    </x-primary-button>
                    <a href="{{ route('bordereaux.show', $bordereau) }}"
                       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                        <x-icon.x-mark class="h-4 w-4" />Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
