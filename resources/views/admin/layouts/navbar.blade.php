<header class="fixed inset-x-0 top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur-xl lg:sticky lg:inset-x-auto lg:top-0">
    <div class="flex h-[85px] items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 sm:gap-4">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="rounded-lg p-2 transition hover:bg-slate-100 lg:hidden"
            >
                <x-heroicon-o-bars-3 class="h-7 w-7 text-slate-700"/>
            </button>

            <div>
                <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-6">
            <div class="hidden h-10 w-px bg-slate-200 sm:block"></div>

            <div x-data="{ open: false }" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-slate-100"
                    :aria-expanded="open"
                >
                    <div class="hidden text-right sm:block">
                        <h3 class="font-semibold text-slate-800">
                            {{ auth()->user()->name }}
                        </h3>

                        <p class="text-xs text-slate-500">
                            @if(auth()->user()->role === 'super_admin')
                                Super Admin
                            @elseif(auth()->user()->role === 'management')
                                Manajemen
                            @else
                                {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                            @endif
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#C58B1A] shadow-md sm:h-11 sm:w-11">
                        <x-heroicon-o-user class="h-5 w-5 text-white sm:h-6 sm:w-6"/>
                    </div>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    x-transition
                    class="absolute right-0 top-full z-[99999] mt-2 w-[240px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                    style="height: 122px;"
                >
                    <div
                        class="flex items-center gap-2.5 border-b border-slate-100 px-3"
                        style="height: 66px;"
                    >
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#C58B1A]">
                            <x-heroicon-o-user class="h-4 w-4 text-white"/>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold leading-4 text-slate-800">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="mt-1 text-[11px] leading-3 text-slate-500">
                                @if(auth()->user()->role === 'super_admin')
                                    Super Admin
                                @elseif(auth()->user()->role === 'management')
                                    Manajemen
                                @else
                                    {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="p-1.5" style="height: 56px;">
                        <form method="POST" action="{{ route('logout') }}" class="h-full">
                            @csrf

                            <button
                                type="submit"
                                class="flex h-full w-full items-center gap-2.5 rounded-lg px-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                            >
                                <x-heroicon-o-arrow-left-on-rectangle class="h-4 w-4"/>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>