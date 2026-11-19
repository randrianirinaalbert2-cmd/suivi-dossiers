{{-- Liste des pièces reçues avec la personne qui les a prises. --}}
@if (! empty($piecesRecues))
    <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-100">
        @foreach ($piecesRecues as $piecePrise)
            <li class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 px-3 py-2 text-sm">
                <span class="font-medium text-gray-900">{{ $piecePrise['piece'] }}</span>
                <span class="text-xs text-gray-500">
                    {{ $piecePrise['agent'] }} ({{ $piecePrise['role'] }}) — {{ $piecePrise['date'] }}
                </span>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-sm text-gray-500">Aucune pièce reçue enregistrée.</p>
@endif
