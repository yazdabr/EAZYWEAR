<section class="pb-8">

    <x-ui.container>

        <div class="flex flex-col items-center justify-between gap-6 border-t border-gray-200 pt-8 md:flex-row">

            {{-- Product Count --}}
            <p class="text-sm text-gray-600">

                Showing

                <span class="font-semibold text-gray-900">

                    1–8

                </span>

                of

                <span class="font-semibold text-gray-900">

                    48

                </span>

                products

            </p>

            {{-- Pagination --}}
            <nav class="flex items-center gap-2">

                {{-- Previous --}}
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">

                    <x-heroicon-o-chevron-left class="h-5 w-5"/>

                </button>

                {{-- Active --}}
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18] font-semibold text-white">

                    1

                </button>

                {{-- Page --}}
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">

                    2

                </button>

                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">

                    3

                </button>

                {{-- Dots --}}
                <span class="px-2 text-gray-500">

                    ...

                </span>

                {{-- Last --}}
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">

                    6

                </button>

                {{-- Next --}}
                <button
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">

                    <x-heroicon-o-chevron-right class="h-5 w-5"/>

                </button>

            </nav>

        </div>

    </x-ui.container>

</section>