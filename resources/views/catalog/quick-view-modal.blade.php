<div
    x-data="quickView"
    x-show="open"
    x-on:quick-view.window="show($event.detail)"
    @keydown.escape.window="close()"
    @mousemove.window="onDrag($event)"
    @mouseup.window="endDrag()"
    @touchmove.window="onDrag($event)"
    @touchend.window="endDrag()"
    x-cloak
    class="fixed inset-0 z-[9999] flex items-end justify-center lg:items-center"
    style="display:none;"
>
    {{-- ================= OVERLAY BACKDROP ================= --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-md"
    ></div>

    {{-- ================= DESKTOP VIEW ================= --}}
    <div class="hidden min-h-screen w-full items-center justify-center p-6 lg:flex">

        <div
            x-show="open"
            @click.outside="close()"
            x-transition:enter="transition duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
            x-transition:enter-start="opacity-0 scale-90 translate-y-8"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition duration-300 ease-[cubic-bezier(0.7,0,0.84,0)]"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative z-10 w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)]"
        >

            {{-- Close Button --}}
            <button
                @click="close()"
                type="button"
                aria-label="Close modal"
                class="absolute right-6 top-6 z-30 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100/80 text-slate-500 shadow-sm backdrop-blur-md transition-all hover:bg-slate-900 hover:text-white active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]"
            >
                <x-heroicon-o-x-mark class="h-5 w-5"/>
            </button>

            <div class="grid lg:grid-cols-12">

                {{-- Product Image --}}
                <div class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 to-slate-100/80 p-8 lg:col-span-5">

                    <img
                        :src="image"
                        :alt="title"
                        class="max-h-[380px] w-full object-contain drop-shadow-xl transition-transform duration-700 ease-out hover:scale-105"
                    >

                </div>

                {{-- Product Information --}}
                <div class="flex flex-col justify-between p-10 lg:col-span-7">

                    <div>

                        {{-- Category --}}
                        <div class="flex items-center gap-2">

                            <span
                                class="inline-block rounded-full bg-[#AE7C18]/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-[#AE7C18]"
                                x-text="series"
                            ></span>

                        </div>

                        {{-- Product Name --}}
                        <h2
                            class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900"
                            x-text="title"
                        ></h2>

                        {{-- Description --}}
                        <p class="mt-4 text-sm leading-relaxed text-slate-500">
                            Premium apparel designed with quality materials,
                            comfortable construction, and customizable design
                            options for teams, communities, businesses, and
                            everyday wear.
                        </p>

                    </div>

                    {{-- Footer Modal --}}
                    <div class="mt-8 border-t border-slate-100 pt-6">

                        <div class="flex items-center justify-between gap-4">

                            {{-- Price --}}
                            <div>

                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                    Starting From
                                </p>

                                <h3
                                    class="mt-0.5 text-3xl font-black tracking-tight text-[#AE7C18]"
                                    x-text="price"
                                ></h3>

                            </div>

                            {{-- View Detail --}}
                            <div class="shrink-0">

                                <a
                                    x-bind:href="productUrl"
                                    @click.stop
                                    class="inline-flex items-center justify-center rounded-full bg-[#AE7C18] px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/25 transition-all hover:bg-[#8F6514] hover:shadow-xl hover:shadow-[#AE7C18]/35"
                                >
                                    View Detail
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================= MOBILE BOTTOM SHEET ================= --}}
    <div
        x-show="open"
        x-transition:enter="transition-transform duration-[1000ms] ease-[cubic-bezier(0.19,1,0.22,1)]"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition transform duration-550 ease-out"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        :style="isDragging || offsetY > 0 ? `transform: translateY(${offsetY}px); transition: none;` : ''"
        @click.outside="close()"
        class="relative z-20 flex h-[85vh] max-h-[85vh] w-full flex-col overflow-hidden rounded-t-[2.5rem] bg-white shadow-2xl lg:hidden"
    >

        {{-- Drag Handle --}}
        <div
            @mousedown="startDrag($event)"
            @touchstart="startDrag($event)"
            class="relative flex shrink-0 select-none touch-none items-center justify-center bg-white py-4"
        >

            <div class="h-1.5 w-12 cursor-grab rounded-full bg-slate-300 active:cursor-grabbing"></div>

            {{-- Close --}}
            <button
                @click.stop="close()"
                type="button"
                class="absolute right-4 top-2.5 z-50 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 shadow-sm transition-all hover:bg-slate-200 active:scale-90"
            >
                <x-heroicon-o-x-mark class="pointer-events-none h-5 w-5"/>
            </button>

        </div>


        {{-- Product Image & Description --}}
        <div class="flex-1 overflow-y-auto overscroll-contain pb-28">

            {{-- Image --}}
            <div class="relative flex aspect-square w-full items-center justify-center bg-slate-100 sm:aspect-video">

                <img
                    :src="image"
                    :alt="title"
                    class="h-full w-full object-cover"
                >

            </div>

            {{-- Information --}}
            <div class="p-6">

                {{-- Category --}}
                <span
                    class="inline-block rounded-full bg-[#AE7C18]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#AE7C18]"
                    x-text="series"
                ></span>

                {{-- Product Name --}}
                <h2
                    class="mt-3 text-2xl font-bold text-slate-900"
                    x-text="title"
                ></h2>

                {{-- Description --}}
                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    Premium apparel designed with quality materials,
                    comfortable construction, and customizable design
                    options for teams, communities, businesses, and
                    everyday wear.
                </p>

            </div>

        </div>


        {{-- Sticky Footer --}}
        <div class="absolute inset-x-0 bottom-0 z-20 border-t border-slate-100 bg-white/95 p-4 backdrop-blur-md">

            <div class="flex items-center gap-4">

                {{-- Price --}}
                <div class="shrink-0">

                    <p class="text-[10px] font-medium uppercase tracking-wider text-slate-400">
                        Starting From
                    </p>

                    <h3
                        class="text-xl font-bold text-[#AE7C18]"
                        x-text="price"
                    ></h3>

                </div>


                {{-- View Detail --}}
                <div class="flex-1">

                    <a
                        x-bind:href="productUrl"
                        @click.stop
                        class="flex w-full items-center justify-center rounded-full bg-[#AE7C18] py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all hover:bg-[#8F6514]"
                    >
                        View Detail
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>