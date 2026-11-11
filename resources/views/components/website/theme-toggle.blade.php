@props([
    'mobile' => false,
])

@php
    $buttonClass = $mobile
        ? 'group theme-toggle relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-slate-600 transition hover:bg-[#AE7C18]/10 hover:text-[#AE7C18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#AE7C18] focus-visible:ring-offset-2 dark:text-slate-300 dark:hover:bg-white/10 dark:focus-visible:ring-offset-[#0F1115]'
        : 'group theme-toggle relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-slate-600 transition-all duration-200 hover:bg-[#AE7C18]/10 hover:text-[#AE7C18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#AE7C18] focus-visible:ring-offset-2 dark:text-slate-300 dark:hover:bg-white/10 dark:focus-visible:ring-offset-[#0F1115]';
@endphp

<button
    type="button"
    x-data
    @click="$store.theme.toggle()"
    aria-label="Toggle color theme"
    :aria-label="$store.theme.dark ? 'Switch to light theme' : 'Switch to dark theme'"
    :aria-pressed="$store.theme.dark.toString()"
    title="Toggle color theme"
    class="{{ $buttonClass }}"
>
    <span class="theme-toggle-icon-wrap" aria-hidden="true">
        <x-heroicon-o-sun class="theme-icon theme-icon-sun"/>
        <x-heroicon-o-moon class="theme-icon theme-icon-moon"/>
    </span>

    @unless($mobile)
        {{-- Tooltip --}}
        <span
            class="pointer-events-none absolute right-0 top-full z-50 mt-2 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1.5 text-[11px] font-medium text-white opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
        >
            <span class="theme-tooltip-light">Dark mode</span>
            <span class="theme-tooltip-dark">Light mode</span>
        </span>
    @endunless
</button>
