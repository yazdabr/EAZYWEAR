<div class="flex flex-col items-center justify-between gap-4 sm:flex-row">

    {{-- Navigation --}}
    <div class="flex items-center gap-2">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())

            <span
                class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-300"
            >
                <x-heroicon-o-chevron-left class="h-5 w-5"/>
            </span>

        @else

            <a
                href="{{ $paginator->previousPageUrl() }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-200 hover:border-[#AE7C18] hover:bg-[#AE7C18] hover:text-white"
            >
                <x-heroicon-o-chevron-left class="h-5 w-5"/>
            </a>

        @endif

        {{-- Page Numbers --}}
        @foreach ($paginator->getUrlRange(
            max(1, $paginator->currentPage() - 2),
            min($paginator->lastPage(), $paginator->currentPage() + 2)
        ) as $page => $url)

            @if ($page == $paginator->currentPage())

                <span
                    class="flex h-10 min-w-[40px] items-center justify-center rounded-xl bg-[#AE7C18] px-3 text-sm font-semibold text-white shadow"
                >
                    {{ $page }}
                </span>

            @else

                <a
                    href="{{ $url }}"
                    class="flex h-10 min-w-[40px] items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition duration-200 hover:border-[#AE7C18] hover:text-[#AE7C18]"
                >
                    {{ $page }}
                </a>

            @endif

        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())

            <a
                href="{{ $paginator->nextPageUrl() }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-200 hover:border-[#AE7C18] hover:bg-[#AE7C18] hover:text-white"
            >
                <x-heroicon-o-chevron-right class="h-5 w-5"/>
            </a>

        @else

            <span
                class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-300"
            >
                <x-heroicon-o-chevron-right class="h-5 w-5"/>
            </span>

        @endif

    </div>

</div>