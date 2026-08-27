<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    @vite(['resources/css/app.css'])
    <style>
        [x-cloak] {
            display: none !important;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
        }
    </style>
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
                action="{{ route('login.store') }}"
                class="space-y-3.5 sm:space-y-5"
            >
                @csrf

                @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-[9px] text-red-600 sm:rounded-xl sm:px-4 sm:py-3 sm:text-xs">
                        <div class="flex items-start gap-2">
                            <x-heroicon-o-exclamation-circle class="mt-0.5 h-3.5 w-3.5 shrink-0 sm:h-4 sm:w-4"/>
                            <div>
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- EMAIL --}}
                <div>
                    <label
                        for="username"
                        class="mb-1.5 block text-[8px] font-semibold uppercase tracking-wider text-slate-600 sm:mb-2 sm:text-xs"
                    >
                        Username
                    </label>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-user class="h-3 w-3 text-slate-400 sm:h-4 sm:w-4"/>
                        </div>

                        <input
                            id="username"
                            name="username"
                            type="text"
                            autocomplete="username"
                            required
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-9 text-[10px] text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-12 sm:rounded-xl sm:px-11 sm:text-sm"
                        >
                    </div>

                    @error('username')
                        <p class="mt-1.5 text-[9px] text-red-500 sm:text-xs">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between sm:mb-2">
                        <label
                            for="password"
                            class="block text-[8px] font-semibold uppercase tracking-wider text-slate-600 sm:text-xs"
                        >
                            Password
                        </label>
                    </div>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-o-lock-closed class="h-3 w-3 text-slate-400 sm:h-4 sm:w-4"/>
                        </div>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-9 text-[10px] text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-12 sm:rounded-xl sm:pl-11 sm:pr-11 sm:text-sm"
                        >

                        <button
                            type="button"
                            id="toggle-password"
                            class="absolute inset-y-0 right-0 z-10 flex w-9 items-center justify-center text-slate-400 transition hover:text-slate-600 sm:w-11"
                            aria-label="Tampilkan password"
                        >
                            <x-heroicon-o-eye
                                id="password-eye"
                                class="h-3 w-3 sm:h-4 sm:w-4"
                            />
                        </button>
                    </div>
                </div>

                {{-- REMEMBER --}}
                <div class="flex items-center">
                    <label for="remember" class="flex cursor-pointer items-center gap-1.5">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
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

                {{-- *BACK TO HOMEPAGE* --}}
                <a
                    href="{{ route('home') }}"
                    class="mt-2 inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-[10px] font-semibold text-slate-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-12 sm:rounded-xl sm:px-5 sm:text-sm"
                >
                    <x-heroicon-o-arrow-left class="h-3 w-3 sm:h-4 sm:w-4"/>
                    <span>Kembali ke Homepage</span>
                </a>
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
    
<script>
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password');
    const toggle = document.getElementById('toggle-password');
    const eye = document.getElementById('password-eye');

    if (!password || !toggle || !eye) return;

    toggle.addEventListener('click', function () {
        const isPassword = password.type === 'password';

        password.type = isPassword ? 'text' : 'password';

        eye.outerHTML = isPassword
            ? `<svg id="password-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 sm:h-4 sm:w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178.047.14.047.294 0 .434C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
            : `<svg id="password-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3 w-3 sm:h-4 sm:w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.85-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.774 3.162 10.066 7.5a10.52 10.52 0 01-4.293 5.348M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.879 9.879"/></svg>`;
    });
});
</script>
</body>
</html>