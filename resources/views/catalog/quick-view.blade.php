<div
    x-data="{ open:false }">

    {{-- Trigger --}}
    <button
        @click="open=true"
        class="rounded-full border border-white/70 bg-white/90 px-5 py-2 text-sm font-semibold text-gray-900 backdrop-blur transition duration-300 hover:bg-[#AE7C18] hover:text-white">

        Quick View

    </button>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 p-6"
        style="display:none;">

        <div
            @click.outside="open=false"
            class="relative w-full max-w-4xl overflow-hidden rounded-3xl bg-white">

            {{-- Close --}}
            <button
                @click="open=false"
                class="absolute right-6 top-6 z-20">

                <x-heroicon-o-x-mark
                    class="h-7 w-7"/>

            </button>

            <div
                class="grid lg:grid-cols-2">

                {{-- Image --}}
                <div>

                    <img
                        src="{{ asset($image) }}"
                        class="aspect-square h-full w-full object-cover">

                </div>

                {{-- Content --}}
                <div
                    class="flex flex-col justify-center p-10">

                    <p
                        class="text-sm font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">

                        {{ $series }}

                    </p>

                    <h2
                        class="mt-3 text-4xl font-bold">

                        {{ $title }}

                    </h2>

                    <p
                        class="mt-6 text-lg text-gray-600">

                        Premium sublimation jersey with breathable dry-fit
                        fabric and unlimited custom design.

                    </p>

                    <div
                        class="mt-8">

                        <p
                            class="text-gray-500">

                            Starting From

                        </p>

                        <h3
                            class="text-3xl font-bold text-[#AE7C18]">

                            {{ $price }}

                        </h3>

                    </div>

                    <div
                        class="mt-10">

                        <x-ui.button
                            href="{{ route('product.detail') }}">

                            View Detail

                        </x-ui.button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>