<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ sidebarOpen: false }"
    class="h-full scroll-smooth"
>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F5F6FA] font-sans text-slate-800 antialiased">

    {{-- ================= MOBILE OVERLAY ================= --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen=false"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
    ></div>

    {{-- ================= SIDEBAR ================= --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 w-[260px] -translate-x-full transform transition-transform duration-300 ease-in-out lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        @include('admin.layouts.sidebar')
    </aside>

    {{-- ================= CONTENT ================= --}}
    <div class="min-h-screen transition-all duration-300 lg:ml-[260px]">

        {{-- Navbar --}}
        @include('admin.layouts.navbar')

        {{-- Main Content --}}
        <main class="p-6 pt-[109px] sm:p-6 sm:pt-[109px] lg:p-8 lg:pt-8">
            @yield('content')
        </main>

    </div>

    {{-- ================= TOAST ================= --}}
    <div
        id="toast-container"
        class="fixed right-6 top-6 z-[9999] space-y-3"
    ></div>

    @stack('scripts')

    <x-admin.toast />

</body>
</html>