<div
    class="flex flex-col items-center justify-between gap-4 sm:flex-row">

    {{-- Left --}}
    <div
        class="text-sm text-slate-500">

        Showing

        <span class="font-semibold text-slate-800">

            1

        </span>

        to

        <span class="font-semibold text-slate-800">

            10

        </span>

        of

        <span class="font-semibold text-slate-800">

            128

        </span>

        results

    </div>

    {{-- Right --}}
    <div
        class="flex items-center gap-2">

        {{-- Previous --}}
        <button
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-200 hover:border-[#AE7C18] hover:bg-[#AE7C18] hover:text-white disabled:cursor-not-allowed disabled:opacity-40">

            <x-heroicon-o-chevron-left
                class="h-5 w-5"/>

        </button>

        {{-- Page 1 --}}
        <button
            class="flex h-10 min-w-[40px] items-center justify-center rounded-xl bg-[#AE7C18] px-3 text-sm font-semibold text-white shadow">

            1

        </button>

        {{-- Page 2 --}}
        <button
            class="flex h-10 min-w-[40px] items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition duration-200 hover:border-[#AE7C18] hover:text-[#AE7C18]">

            2

        </button>

        {{-- Page 3 --}}
        <button
            class="flex h-10 min-w-[40px] items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition duration-200 hover:border-[#AE7C18] hover:text-[#AE7C18]">

            3

        </button>

        {{-- Dots --}}
        <span
            class="px-1 text-slate-400">

            ...

        </span>

        {{-- Last --}}
        <button
            class="flex h-10 min-w-[40px] items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition duration-200 hover:border-[#AE7C18] hover:text-[#AE7C18]">

            13

        </button>

        {{-- Next --}}
        <button
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition duration-200 hover:border-[#AE7C18] hover:bg-[#AE7C18] hover:text-white">

            <x-heroicon-o-chevron-right
                class="h-5 w-5"/>

        </button>

    </div>

</div>