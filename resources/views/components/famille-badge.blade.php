@props(['famille'])

@php
    $familleEnum = $famille instanceof \App\Enums\FamilleMouvement
        ? $famille
        : \App\Enums\FamilleMouvement::tryFrom((string) $famille);
@endphp

@if ($familleEnum)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$familleEnum->couleur()]) }}>
        <span class="font-mono">{{ $familleEnum->numero() }}</span>
        {{ $familleEnum->label() }}
    </span>
@endif
