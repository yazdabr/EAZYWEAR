@props([
    'title',
    'value',
    'icon',
    'growth' => null,
    'positive' => true,
    'neutral' => false,
    'iconBg' => 'bg-[#FDF8EE]',
    'iconColor' => 'text-[#C4902C]',
])

<div class="flex flex-col justify-between rounded-xl bg-white p-3.5 border border-slate-100 shadow-sm transition-all duration-300 hover:shadow-md sm:rounded-2xl sm:p-5">
    
    {{-- Baris Atas: Icon & Growth Indicator --}}
    <div class="flex items-center justify-between">
        
        {{-- Icon Box --}}
        <div class="flex h-9 w-9 items-center justify-center rounded-lg sm:h-11 sm:w-11 sm:rounded-xl {{ $iconBg }} {{ $iconColor }}">
            {{ $icon }}
        </div>

        {{-- Growth Indicator --}}
        @if($growth)
            <div class="flex items-center gap-0.5 text-[10px] font-bold sm:gap-1 sm:text-xs">
                @if($neutral)
                    <span class="text-slate-400 font-bold">—</span>
                    <span class="text-slate-500">{{ $growth }}</span>
                @elseif($positive)
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5 text-emerald-600 stroke-[2.5] sm:h-4 sm:w-4" />
                    <span class="text-emerald-600">{{ $growth }}</span>
                @else
                    <x-heroicon-o-arrow-trending-down class="h-3.5 w-3.5 text-rose-600 stroke-[2.5] sm:h-4 sm:w-4" />
                    <span class="text-rose-600">{{ $growth }}</span>
                @endif
            </div>
        @endif

    </div>

    {{-- Baris Bawah: Title & Value --}}
    <div class="mt-3 sm:mt-5">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-[11px]">
            {{ $title }}
        </p>

        <h3 class="mt-0.5 text-lg font-bold tracking-tight text-slate-900 sm:mt-1 sm:text-2xl">
            {{ $value }}
        </h3>
    </div>

</div>