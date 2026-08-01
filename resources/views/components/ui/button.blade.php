@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $base = 'inline-flex w-full sm:w-auto items-center justify-center rounded-full px-6 py-3 font-medium transition-all duration-300';

    $classes = match ($variant) {
        'secondary' => 'bg-white text-black border border-gray-300 hover:bg-gray-100',

        'outline' => 'border border-[#AE7C18] text-[#AE7C18] hover:bg-[#AE7C18] hover:text-white hover:scale-105',

        default => 'bg-[#AE7C18] text-white shadow-lg hover:bg-[#96690F] hover:scale-105',
    };
@endphp

@if ($href)

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => $base . ' ' . $classes
    ]) }}>

    {{ $slot }}

</a>

@else

<button
    {{ $attributes->merge([
        'class' => $base . ' ' . $classes
    ]) }}>

    {{ $slot }}

</button>

@endif