<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Eazywear Indonesia')</title>

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

<body class="bg-white text-gray-900">
    <x-website.navbar />

    <main class="pt-20">
        @yield('content')
    </main>

    <x-website.footer />

    @stack('scripts')
</body>
</html>