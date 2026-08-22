@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $base = 'inline-flex w-full sm:w-auto items-center justify-center rounded-full px-5 py-2.5 sm:px-7 sm:py-3.5 text-xs sm:text-base font-semibold tracking-wide transition-all duration-300 active:scale-95';

    $classes = match ($variant) {
        'secondary' => 'bg-white text-gray-900 border border-gray-300 hover:bg-gray-100 shadow-sm',

        'outline' => 'border border-[#AE7C18] bg-white/80 backdrop-blur-sm text-[#AE7C18] hover:bg-[#AE7C18] hover:text-white sm:hover:scale-105 shadow-sm',

        default => 'bg-[#AE7C18] text-white shadow-md hover:bg-[#96690F] sm:hover:scale-105',
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