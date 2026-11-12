@props(['pole'])

@php
    $poleEnum = $pole instanceof \App\Enums\PoleDossier
        ? $pole
        : \App\Enums\PoleDossier::from((string) $pole);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$poleEnum->couleur()]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $poleEnum->label() }}
</span>
