@props([
    'name' => 'category',
    'placeholder' => 'Select Option',
    'options' => [],
    'selected' => '',
])

<div
    x-data="{
        open: false,

        selectedValue: @js($selected ?? ''),

        selectedLabel: @js(
            $selected
                ? ($options[$selected] ?? $placeholder)
                : $placeholder
        ),

        selectOption(value, label) {
            this.selectedValue = value;
            this.selectedLabel = label;
            this.open = false;

            const form = this.$el.closest('form');

            if (form) {
                const input = form.querySelector(
                    '[name=\'{{ $name }}\']'
                );

                if (input) {
                    input.value = value;
                }

                form.submit();
            }
        }
    }"
    class="relative w-full sm:w-60"
>

    {{-- ================================================= --}}
    {{-- HIDDEN INPUT --}}
    {{-- ================================================= --}}

    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ $selected }}"
        x-ref="filterInput"
    >


    {{-- ================================================= --}}
    {{-- BUTTON --}}
    {{-- ================================================= --}}

    <button
        type="button"
        @click="open = !open"
        class="flex h-[50px] w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-[#AE7C18] focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/15"
    >

        <div class="flex min-w-0 items-center gap-2">

            <x-heroicon-o-funnel
                class="h-5 w-5 shrink-0 text-slate-400"
            />

            <span
                x-text="selectedLabel"
                class="truncate"
            ></span>

        </div>


        <x-heroicon-o-chevron-down
            class="h-4 w-4 shrink-0 text-slate-400 transition duration-200"
            x-bind:class="{ 'rotate-180': open }"
        />

    </button>


    {{-- ================================================= --}}
    {{-- DROPDOWN --}}
    {{-- ================================================= --}}

    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"

        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"

        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"

        class="absolute left-0 right-0 z-[150] mt-2 max-h-64 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-xl"

        style="display: none;"
    >

        {{-- ================================================= --}}
        {{-- SEMUA KATEGORI --}}
        {{-- ================================================= --}}

        <button
            type="button"

            @click="
                selectOption('', @js($placeholder))
            "

            class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition duration-150 hover:bg-slate-50 hover:text-[#AE7C18]"
        >

            <span>
                {{ $placeholder }}
            </span>


            <template x-if="selectedValue === ''">

                <x-heroicon-o-check
                    class="h-4 w-4 text-[#AE7C18]"
                />

            </template>

        </button>


        {{-- ================================================= --}}
        {{-- DAFTAR KATEGORI --}}
        {{-- ================================================= --}}

        @foreach($options as $value => $label)

            <button
                type="button"

                @click="
                    selectOption(
                        @js((string) $value),
                        @js($label)
                    )
                "

                class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition duration-150 hover:bg-slate-50 hover:text-[#AE7C18]"
            >

                <span class="truncate">
                    {{ $label }}
                </span>


                <template
                    x-if="String(selectedValue) === String(@js((string) $value))"
                >

                    <x-heroicon-o-check
                        class="h-4 w-4 shrink-0 text-[#AE7C18]"
                    />

                </template>

            </button>

        @endforeach

    </div>

</div>