@props([
    'utilisateur' => null,
    'taille' => 'h-10 w-10',
    'texte' => 'text-sm',
    'contour' => '',
])

@php
    $utilisateur = $utilisateur ?? auth()->user();
    $url = $utilisateur?->urlPhoto();
    $initiales = $utilisateur?->initiales() ?? '?';
    $nom = $utilisateur?->nom ?? 'Utilisateur';
@endphp

@if ($url)
    <img src="{{ $url }}" alt="Photo de {{ $nom }}"
         {{ $attributes->merge(['class' => trim("$taille rounded-full object-cover $contour")]) }}>
@else
    <span aria-hidden="true" title="{{ $nom }}"
          {{ $attributes->merge(['class' => trim("$taille inline-flex items-center justify-center rounded-full bg-[linear-gradient(135deg,#38bdf8,#22d3ee)] $texte font-semibold text-white $contour")]) }}>
        {{ $initiales }}
    </span>
@endif
