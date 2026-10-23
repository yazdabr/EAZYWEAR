@props([
    'products' => [],
    'topProductsMax' => 0
])

<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm sm:rounded-3xl">
    {{-- Card Header --}}
    <div class="border-b border-slate-100 p-4 sm:p-6">
        <h3 class="text-base font-bold text-slate-900 sm:text-xl">Produk Terlaris</h3>
        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Berdasarkan volume penjualan</p>
    </div>

    {{-- Product List Container --}}
    <div class="max-h-[380px] space-y-3 overflow-y-auto p-3 sm:max-h-[430px] sm:space-y-4 sm:p-5">
        @forelse($products as $index => $product)
            <div class="group rounded-xl border border-slate-100 bg-slate-50/50 p-3 transition hover:border-[#AE7C18]/40 hover:bg-white hover:shadow-sm sm:rounded-2xl sm:p-4">
                <div class="flex items-center gap-3">
                    
                    {{-- Product Image --}}
                    <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200/80 bg-white sm:h-14 sm:w-14 sm:rounded-xl">
                        @if(!empty($product->image))
                            <img
                                src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . ltrim($product->image, '/')) }}"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="hidden h-full w-full items-center justify-center">
                                <x-heroicon-o-cube class="h-5 w-5 text-slate-300 sm:h-6 sm:w-6"/>
                            </div>
                        @else
                            <x-heroicon-o-cube class="h-5 w-5 text-slate-300 sm:h-6 sm:w-6"/>
                        @endif

                        {{-- Rank Badge --}}
                        <span class="absolute left-0 top-0 rounded-br-md bg-slate-900/80 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">
                            #{{ $index + 1 }}
                        </span>
                    </div>

                    {{-- Product Details --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1.5">
                            <h4 class="truncate text-xs font-bold text-slate-900 sm:text-sm">
                                {{ $product->name }}
                            </h4>

                            @if($index === 0)
                                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[9px] font-bold text-[#AE7C18] sm:text-[10px]">
                                    Top 1
                                </span>
                            @endif
                        </div>

                        <div class="mt-1 flex items-center justify-between text-[11px] sm:text-xs">
                            <span class="font-bold text-[#AE7C18]">
                                Rp {{ number_format($product->total_sales, 0, ',', '.') }}
                            </span>
                            <span class="font-medium text-slate-500">
                                {{ $product->total_qty }} terjual
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200/60">
                            <div
                                class="h-full rounded-full bg-[#AE7C18] transition-all duration-500"
                                style="width: {{ $topProductsMax > 0 ? ($product->total_qty / $topProductsMax) * 100 : 0 }}%"
                            ></div>
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-8 text-center sm:py-12">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-cube class="h-5 w-5 text-slate-300 sm:h-6 sm:w-6"/>
                </div>

                <p class="mt-3 text-xs font-semibold text-slate-700 sm:text-sm">
                    Belum ada data penjualan
                </p>
                <p class="mt-0.5 text-[11px] text-slate-400">
                    Data akan muncul setelah transaksi dibuat.
                </p>
            </div>
        @endforelse
    </div>
</div>