@props([
    'route',
    'mobile' => false,
])

@php
    $active = request()->routeIs($route . '*');

    if ($mobile) {
        $classes = $active
            ? 'flex items-center justify-between rounded-xl bg-[#AE7C18]/10 px-4 py-3 text-base font-semibold text-[#AE7C18] transition'
            : 'flex items-center justify-between rounded-xl px-4 py-3 text-base font-medium text-gray-700 transition hover:bg-gray-50 hover:text-[#AE7C18]';
    } else {
        $classes = $active
            ? 'border-b-2 border-[#AE7C18] pb-2 text-sm font-semibold text-[#AE7C18] transition duration-300'
            : 'pb-2 text-sm font-semibold text-gray-900 transition duration-300 hover:text-[#AE7C18]';
    }
@endphp

<a
    href="{{ route($route) }}"
    {{ $attributes->merge([
        'class' => $classes
    ]) }}>

    @if($mobile)

        <span>{{ $slot }}</span>

        <x-heroicon-o-chevron-right
            class="h-5 w-5 {{ $active ? 'text-[#AE7C18]' : 'opacity-40' }}" />

    @else

        {{ $slot }}

    @endif

</a>