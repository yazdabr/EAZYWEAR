@php
    $currentCategory = request('category');
@endphp

<a
    href="{{ route('catalog', array_filter(['search' => request('search')])) }}"
    class="{{ !$currentCategory
        ? 'border-[#AE7C18] bg-[#AE7C18] text-white shadow-md'
        : 'border-slate-200/80 bg-white text-slate-600 hover:border-[#AE7C18] hover:text-[#AE7C18] shadow-md' }}
        box-border flex h-[42px] shrink-0 items-center whitespace-nowrap rounded-full border px-5 text-xs font-semibold tracking-wide transition-all duration-200"
>
    All
</a>

@foreach($categories as $category)
    <a
        href="{{ route('catalog', array_filter([
            'search' => request('search'),
            'category' => $category->id
        ])) }}"
        class="{{ $currentCategory == $category->id
            ? 'border-[#AE7C18] bg-[#AE7C18] text-white shadow-md'
            : 'border-slate-200/80 bg-white text-slate-600 hover:border-[#AE7C18] hover:text-[#AE7C18] shadow-md' }}
            box-border flex h-[42px] shrink-0 items-center whitespace-nowrap rounded-full border px-5 text-xs font-semibold tracking-wide transition-all duration-200"
    >
        {{ $category->name }}
    </a>
@endforeach