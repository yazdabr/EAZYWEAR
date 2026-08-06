<header
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-xl">

    <div
        class="flex h-[85px] items-center justify-between px-6 lg:px-8">

        {{-- ================= LEFT ================= --}}
        <div class="flex items-center gap-4">

            {{-- Mobile Toggle --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="rounded-lg p-2 transition hover:bg-slate-100 lg:hidden">

                <x-heroicon-o-bars-3
                    class="h-7 w-7 text-slate-700"/>

            </button>

            {{-- Page Title --}}
            <div>

                <h1
                    class="text-2xl font-bold text-slate-800">

                    @yield('page-title','Dashboard')

                </h1>

            </div>

        </div>

        {{-- ================= RIGHT ================= --}}
        <div
            class="flex items-center gap-6">

            {{-- Notification --}}
            <button
                class="relative rounded-xl p-2 transition hover:bg-slate-100">

                <x-heroicon-o-bell
                    class="h-6 w-6 text-slate-600"/>

                <span
                    class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">

                    3

                </span>

            </button>

            {{-- Divider --}}
            <div
                class="hidden h-10 w-px bg-slate-200 sm:block">
            </div>

            {{-- User --}}
            <button
                class="flex items-center gap-3 rounded-xl transition hover:bg-slate-100 p-2">

                {{-- Name --}}
                <div
                    class="hidden text-right sm:block">

                    <h3
                        class="font-semibold text-slate-800">

                        Admin Eazywear

                    </h3>

                    <p
                        class="text-xs text-slate-500">

                        Super Admin

                    </p>

                </div>

                {{-- Avatar --}}
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-[#C58B1A] shadow-md">

                    <x-heroicon-o-user
                        class="h-6 w-6 text-white"/>

                </div>

            </button>

        </div>

    </div>

</header>