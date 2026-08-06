@props([

    'placeholder' => 'Select Option',

    'options' => [],

])

<div
    x-data="{
        open:false,
        selected:'{{ $placeholder }}'
    }"
    class="relative w-full sm:w-60">

    {{-- Button --}}
    <button
        @click="open=!open"
        type="button"
        class="flex h-[50px] w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-[#AE7C18] focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/15">

        <div
            class="flex items-center gap-2">

            <x-heroicon-o-funnel
                class="h-5 w-5 text-slate-400"/>

            <span
                x-text="selected"
                class="truncate">

            </span>

        </div>

        <x-heroicon-o-chevron-down
            class="h-4 w-4 text-slate-400 transition duration-200"
            ::class="{ 'rotate-180': open }"/>

    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-cloak

        @click.outside="open=false"

        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"

        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"

        class="absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">

        @foreach($options as $option)

            <button

                type="button"

                @click="
                    selected='{{ $option }}';
                    open=false;
                "

                class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition duration-150 hover:bg-slate-50 hover:text-[#AE7C18]">

                <span>

                    {{ $option }}

                </span>

                <template
                    x-if="selected==='{{ $option }}'">

                    <x-heroicon-o-check
                        class="h-4 w-4 text-[#AE7C18]"/>

                </template>

            </button>

        @endforeach

    </div>

</div>