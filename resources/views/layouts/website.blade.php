<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {{-- ================= SEO ================= --}}

    <title>
        @yield('title', 'Eazywear Indonesia')
    </title>

    <meta
        name="description"
        content="@yield('meta_description', 'Eazywear Indonesia menyediakan custom sportswear, jersey, teamwear, dan apparel berkualitas untuk tim, komunitas, sekolah, dan bisnis.')"
    >

    <meta
        name="robots"
        content="@yield('robots', 'index, follow')"
    >

    {{-- ================= CANONICAL ================= --}}

    <link
        rel="canonical"
        href="@yield('canonical', url()->current())"
    >

    {{-- ================= OPEN GRAPH ================= --}}

    <meta
        property="og:type"
        content="@yield('og_type', 'website')"
    >

    <meta
        property="og:title"
        content="@yield('og_title', trim($__env->yieldContent('title')) ?: 'Eazywear Indonesia')"
    >

    <meta
        property="og:description"
        content="@yield('og_description', trim($__env->yieldContent('meta_description')) ?: 'Eazywear Indonesia menyediakan custom sportswear, jersey, teamwear, dan apparel berkualitas.')"
    >

    <meta
        property="og:url"
        content="@yield('og_url', url()->current())"
    >

    <meta
        property="og:site_name"
        content="Eazywear Indonesia"
    >

    <meta
        property="og:locale"
        content="id_ID"
    >

    <meta
        property="og:image"
        content="@yield('og_image', asset('images/hero/logo.png'))"
    >

    {{-- ================= TWITTER / X ================= --}}

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="@yield('twitter_title', trim($__env->yieldContent('title')) ?: 'Eazywear Indonesia')"
    >

    <meta
        name="twitter:description"
        content="@yield('twitter_description', trim($__env->yieldContent('meta_description')) ?: 'Eazywear Indonesia menyediakan custom sportswear, jersey, teamwear, dan apparel berkualitas.')"
    >

    <meta
        name="twitter:image"
        content="@yield('twitter_image', asset('images/hero/logo.png'))"
    >

    {{-- ================= FAVICON ================= --}}

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/hero/logo.png') }}"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('images/hero/logo.png') }}"
    >

    {{-- ================= ASSETS ================= --}}

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
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