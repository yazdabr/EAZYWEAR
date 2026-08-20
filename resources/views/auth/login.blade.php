<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    @vite(['resources/css/app.css'])
</head>

<body class="relative min-h-screen font-sans antialiased text-slate-900 selection:bg-[#AE7C18] selection:text-white">

    {{-- BACKGROUND --}}
    <div
        class="absolute inset-0 -z-20 h-full w-full bg-cover bg-center"
        style="background-image:url('{{ asset('images/hero/bgweb.png') }}');"
    ></div>

    {{-- DARK OVERLAY --}}
    <div class="absolute inset-0 -z-10 bg-slate-950/75 backdrop-blur-md"></div>

    <div class="flex min-h-screen flex-col items-center justify-center px-3 py-5 sm:px-6 sm:py-10 lg:px-8">

        {{-- LOGO --}}
        <div class="mb-4 text-center sm:mb-7">
            <div class="inline-flex h-10 items-center justify-center sm:h-14">
                <img
                    src="{{ asset('images/hero/logoo.png') }}"
                    alt="Logo"
                    class="h-full w-auto object-contain drop-shadow-[0_3px_8px_rgba(0,0,0,0.35)]"
                >
            </div>
        </div>

        {{-- LOGIN CARD --}}
        <div class="w-full max-w-[280px] rounded-2xl border border-white/30 bg-white p-4 shadow-[0_20px_60px_rgba(0,0,0,0.35)] sm:max-w-md sm:rounded-3xl sm:p-10">

            {{-- HEADING --}}
            <div class="mb-4 text-center sm:mb-8">
                <h2 class="text-lg font-bold tracking-tight text-slate-900 sm:text-2xl">
                    Selamat Datang
                </h2>

                <p class="mt-1.5 text-[9px] leading-relaxed text-slate-500 sm:mt-2 sm:text-sm">
                    Masukkan kredensial Anda untuk mengakses dashboard.
                </p>
            </div>

            <form
                method="POST"
                action="#"
                class="space-y-3.5 sm:space-y-5"
            >
                @csrf

                {{-- EMAIL --}}
                <div>
                    <label
                        for="email"
                        class="mb-1.5 block text-[8px] font-semibold uppercase tracking-wider text-slate-600 sm:mb-2 sm:text-xs"
                    >
                        Email
                    </label>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-envelope class="h-3 w-3 text-slate-400 sm:h-4 sm:w-4"/>
                        </div>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="nama@domain.com"
                            class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-9 text-[10px] text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-12 sm:rounded-xl sm:px-11 sm:text-sm"
                        >
                    </div>
                </div>

                {{-- PASSWORD --}}
                <div x-data="{ show:false }">
                    <div class="mb-1.5 flex items-center justify-between sm:mb-2">
                        <label
                            for="password"
                            class="block text-[8px] font-semibold uppercase tracking-wider text-slate-600 sm:text-xs"
                        >
                            Password
                        </label>

                        <a
                            href="#"
                            class="text-[8px] font-medium text-[#AE7C18] transition hover:underline sm:text-xs"
                        >
                            Lupa password?
                        </a>
                    </div>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-lock-closed class="h-3 w-3 text-slate-400 sm:h-4 sm:w-4"/>
                        </div>

                        <input
                            id="password"
                            name="password"
                            :type="show ? 'text' : 'password'"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-9 text-[10px] text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-12 sm:rounded-xl sm:pl-11 sm:pr-11 sm:text-sm"
                        >

                        <button
                            type="button"
                            @click="show=!show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-600"
                        >
                            <x-heroicon-o-eye
                                x-show="!show"
                                class="h-3 w-3 sm:h-4 sm:w-4"
                            />

                            <x-heroicon-o-eye-slash
                                x-show="show"
                                x-cloak
                                class="h-3 w-3 sm:h-4 sm:w-4"
                            />
                        </button>
                    </div>
                </div>

                {{-- REMEMBER --}}
                <div class="flex items-center">
                    <label class="flex cursor-pointer items-center gap-1.5">
                        <input
                            type="checkbox"
                            name="remember"
                            class="h-3 w-3 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18] sm:h-4 sm:w-4"
                        >

                        <span class="text-[8px] font-medium text-slate-600 sm:text-xs">
                            Ingat saya di perangkat ini
                        </span>
                    </label>
                </div>

                {{-- SUBMIT --}}
                <button
                    type="submit"
                    class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-[#AE7C18] px-3 text-[10px] font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition hover:bg-[#96690F] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/20 active:scale-[0.99] sm:h-12 sm:rounded-xl sm:px-5 sm:text-sm"
                >
                    <span>Masuk ke Dashboard</span>
                    <x-heroicon-o-arrow-right class="h-3 w-3 sm:h-4 sm:w-4"/>
                </button>
            </form>

            {{-- SECURITY --}}
            <div class="mt-4 flex items-center justify-center gap-1.5 border-t border-slate-100 pt-3 text-center sm:mt-8 sm:gap-2 sm:pt-6">
                <x-heroicon-o-shield-check class="h-3 w-3 text-emerald-600 sm:h-4 sm:w-4"/>

                <span class="text-[7px] font-medium text-slate-400 sm:text-[11px]">
                    Koneksi aman dan terenkripsi
                </span>
            </div>
        </div>

        {{-- FOOTER --}}
        <p class="mt-4 text-center text-[8px] text-white/60 sm:mt-8 sm:text-xs">
            © {{ date('Y') }} Sales Management System. All rights reserved.
        </p>
    </div>

</body>
</html>