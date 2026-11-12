@props(['disabled' => false])

@php
    $avecIcone = isset($icone);
    $avecAction = isset($action);
    $classes = trim(
        'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm'
        . ($avecIcone ? ' ps-10' : '')
        . ($avecAction ? ' pe-11' : '')
    );
@endphp

@if ($avecIcone || $avecAction)
    <div class="relative">
        @if ($avecIcone)
            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-gray-400">
                {{ $icone }}
            </span>
        @endif

        <input @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>

        @if ($avecAction)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3">
                {{ $action }}
            </div>
        @endif
    </div>
@else
    <input @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
@endif
