@php
    $categories = [
        'All',
        'Jersey',
        'Futsal',
        'Basketball',
        'Volleyball',
        'Cycling',
        'Esports',
    ];
@endphp

{{-- Scrollable di mobile (no-scrollbar), Flex Wrap di Desktop --}}
<div class="flex items-center gap-2.5 overflow-x-auto pb-2 pt-1 lg:flex-wrap lg:overflow-visible lg:pb-0 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
    @foreach($categories as $index => $category)
        <button
            type="button"
            class="{{ $index == 0
                ? 'bg-[#AE7C18] text-white shadow-md shadow-[#AE7C18]/25 border-[#AE7C18]'
                : 'bg-white text-slate-600 border-slate-200/80 hover:border-[#AE7C18] hover:text-[#AE7C18] hover:shadow-sm' }}
                whitespace-nowrap rounded-full border px-5 py-2.5 text-xs font-semibold tracking-wide transition-all duration-200 active:scale-95 shrink-0"
        >
            {{ $category }}
        </button>
    @endforeach
</div>