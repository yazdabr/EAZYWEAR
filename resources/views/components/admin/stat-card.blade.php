@props([
    'title',
    'value',
    'icon',
    'growth' => null,
    'positive' => true,
    'neutral' => false,
])

<div class="flex flex-col justify-between rounded-2xl bg-white p-5 border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-md">
    
    {{-- Baris Atas: Icon (Kiri) & Growth (Kanan) --}}
    <div class="flex items-center justify-between">
        
        {{-- Icon --}}
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#FDF8EE] text-[#C4902C]">
            {{ $icon }}
        </div>

        {{-- Growth Indicator --}}
        @if($growth)
            <div class="flex items-center gap-1 font-semibold text-xs">
                @if($neutral)
                    <span class="text-gray-400 font-bold text-sm">—</span>
                    <span class="text-gray-500">{{ $growth }}</span>
                @elseif($positive)
                    <x-heroicon-o-arrow-trending-up class="h-4 w-4 text-emerald-600 stroke-[2.5]" />
                    <span class="text-emerald-600">{{ $growth }}</span>
                @else
                    <x-heroicon-o-arrow-trending-down class="h-4 w-4 text-red-600 stroke-[2.5]" />
                    <span class="text-red-600">{{ $growth }}</span>
                @endif
            </div>
        @endif

    </div>

    {{-- Baris Bawah: Title & Value --}}
    <div class="mt-5">
        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
            {{ $title }}
        </p>

        <h3 class="mt-1 text-2xl font-semibold text-gray-900 tracking-tight">
            {{ $value }}
        </h3>
    </div>

</div>