<section class="pb-8">
    <x-ui.container>
        <div class="flex flex-col items-center justify-between gap-6 border-t border-gray-200 pt-8 md:flex-row">
            <p class="text-sm text-gray-600">
                Showing
                <span class="font-semibold text-gray-900">
                    {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                </span>
                of
                <span class="font-semibold text-gray-900">
                    {{ $products->total() }}
                </span>
                products
            </p>

            <div class="flex items-center gap-2">
                {{-- Previous --}}
                @if($products->onFirstPage())
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-300">
                        <x-heroicon-o-chevron-left class="h-5 w-5"/>
                    </span>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">
                        <x-heroicon-o-chevron-left class="h-5 w-5"/>
                    </a>
                @endif

                {{-- Page --}}
                @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                    @if($page == $products->currentPage())
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18] font-semibold text-white">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">
                        <x-heroicon-o-chevron-right class="h-5 w-5"/>
                    </a>
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-300">
                        <x-heroicon-o-chevron-right class="h-5 w-5"/>
                    </span>
                @endif
            </div>
        </div>
    </x-ui.container>
</section>