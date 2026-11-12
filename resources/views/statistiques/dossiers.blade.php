<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Statistiques des dossiers</h2>
                <p class="mt-1 text-sm text-gray-500">Répartition des dossiers par type (Intégration, Titularisation, Avenant, etc.)</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6 transition-all duration-200" :class="menuOuvert ? 'max-w-7xl' : 'max-w-full'">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('statistiques.dossiers') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
                    <div>
                        <x-input-label for="du" value="Du" />
                        <x-text-input id="du" name="du" type="date" class="mt-1 block w-full" :value="$filtres['du'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="au" value="Au" />
                        <x-text-input id="au" name="au" type="date" class="mt-1 block w-full" :value="$filtres['au'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="pole" value="Pôle" />
                        <select id="pole" name="pole" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Tous</option>
                            @foreach ($poles as $pole)
                                <option value="{{ $pole->value }}" @selected(($filtres['pole'] ?? '') === $pole->value)>{{ $pole->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="statut" value="Statut" />
                        <select id="statut" name="statut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Tous</option>
                            @foreach ($statuts as $statut)
                                <option value="{{ $statut->value }}" @selected(($filtres['statut'] ?? '') === $statut->value)>{{ $statut->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (Auth::user()->voitToutesLesDivisions())
                        <div>
                            <x-input-label for="division_id" value="Division" />
                            <select id="division_id" name="division_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Toutes</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(($filtres['division_id'] ?? '') == $division->id)>{{ $division->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex items-end gap-2">
                        <x-primary-button>Filtrer</x-primary-button>
                        <a href="{{ route('statistiques.dossiers') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Réinitialiser</a>
                    </div>
                </form>
            </div>

            <div @class([
                'grid grid-cols-1 gap-4 sm:grid-cols-2',
                'lg:grid-cols-4' => $voitVisa && $voitSolde,
                'lg:grid-cols-3' => ! ($voitVisa && $voitSolde),
            ])>
                <div class="rounded-xl border border-indigo-100 bg-[linear-gradient(135deg,#eef2ff_0%,#ffffff_75%)] p-5 shadow-sm shadow-indigo-950/5">
                    <div class="text-xs font-semibold uppercase tracking-wide text-indigo-800/70">Total des dossiers</div>
                    <div class="mt-2 text-3xl font-bold text-indigo-900">{{ $totalGlobal }}</div>
                </div>
                @if ($voitVisa)
                    <div class="rounded-xl border border-teal-100 bg-[linear-gradient(135deg,#f0fdfa_0%,#ffffff_75%)] p-5 shadow-sm shadow-teal-950/5">
                        <div class="text-xs font-semibold uppercase tracking-wide text-teal-800/70">Pôle Visa</div>
                        <div class="mt-2 text-3xl font-bold text-teal-900">{{ $parPole[\App\Enums\PoleDossier::Visa->value] ?? 0 }}</div>
                    </div>
                @endif
                @if ($voitSolde)
                    <div class="rounded-xl border border-violet-100 bg-[linear-gradient(135deg,#f5f3ff_0%,#ffffff_75%)] p-5 shadow-sm shadow-violet-950/5">
                        <div class="text-xs font-semibold uppercase tracking-wide text-violet-800/70">Pôle Solde</div>
                        <div class="mt-2 text-3xl font-bold text-violet-900">{{ $parPole[\App\Enums\PoleDossier::Solde->value] ?? 0 }}</div>
                    </div>
                @endif
                <div class="rounded-xl border border-sky-100 bg-[linear-gradient(135deg,#f0f9ff_0%,#ffffff_75%)] p-5 shadow-sm shadow-sky-950/5">
                    <div class="text-xs font-semibold uppercase tracking-wide text-sky-800/70">Types différents</div>
                    <div class="mt-2 text-3xl font-bold text-sky-900">{{ $stats->count() }}</div>
                </div>
            </div>

            @if(isset($resume))
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">État des dossiers (selon filtres)</h3>
                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-800 ring-1 ring-amber-200">En instance : {{ $resume['en_instance'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 font-medium text-rose-800 ring-1 ring-rose-200">Rejetés : {{ $resume['rejetes'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-800 ring-1 ring-emerald-200">Traités / transmis / archivés : {{ $resume['finalises'] ?? 0 }}</span>
                </div>
            </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Répartition par type de dossier</h3>
                    <div class="text-xs text-gray-500">Dossiers arrivés / enregistrés selon les filtres</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Code</th>
                                <th class="px-6 py-3">Type de dossier</th>
                                <th class="px-6 py-3">Division</th>
                                <th class="px-6 py-3 text-right">Nombre de dossiers</th>
                                <th class="px-6 py-3 text-right">Pourcentage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($stats as $ligne)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $ligne['code'] }}</td>
                                    <td class="px-6 py-3 text-gray-900 font-medium">{{ $ligne['libelle'] }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $ligne['division'] ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-semibold text-gray-900">{{ $ligne['total'] }}</td>
                                    <td class="px-6 py-3 text-right text-gray-600">
                                        {{ $totalGlobal > 0 ? number_format(($ligne['total'] / $totalGlobal) * 100, 1, ',', ' ') : 0 }} %
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">Aucun dossier trouvé pour les filtres sélectionnés.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($stats->isNotEmpty())
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total</td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">{{ $totalGlobal }}</td>
                                    <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700">100,0 %</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
