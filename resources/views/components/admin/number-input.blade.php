@props([
    'prefix' => null,
    'placeholder' => '0',
])

<div class="relative">
    @if($prefix)
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-500">{{ $prefix }}</span>
    @endif
    <input
        type="number"
        min="0"
        step="1"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => ($prefix ? 'pl-12 pr-4 ' : 'px-4 ') . 'w-full rounded-xl border border-slate-300 bg-slate-50 py-3 text-sm text-slate-700 placeholder:text-slate-400 transition-all duration-200 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10'
        ]) }}>
</div>