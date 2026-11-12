@props(['class' => 'h-5 w-5'])

<svg {{ $attributes->merge(['class' => $class]) }} fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5v9A2.5 2.5 0 0 0 5.5 19h13a2.5 2.5 0 0 0 2.5-2.5v-6A2.5 2.5 0 0 0 18.5 8H12l-1.6-1.9A2 2 0 0 0 8.9 5.3H5.5A2.5 2.5 0 0 0 3 7.5Z" />
    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5h18" />
</svg>
