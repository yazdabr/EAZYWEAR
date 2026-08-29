@extends('layouts.website')

@section('title', 'About Eazywear Indonesia | Our Story')
@section('meta_description', 'Learn more about Eazywear Indonesia, our story, craftsmanship, innovation, and commitment to creating quality sportswear for teams, communities, schools, and businesses.')

@section('canonical', rtrim(config('app.url'), '/') . '/about')

@section('og_title', 'About Eazywear Indonesia')
@section('og_description', 'Discover the story behind Eazywear Indonesia and our commitment to quality sportswear.')
@section('og_url', rtrim(config('app.url'), '/') . '/about')

@push('schema')
@php
    $siteUrl = rtrim(config('app.url'), '/');

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $siteUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'About',
                'item' => $siteUrl . '/about',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
    @include('about.hero')
    @include('about.story')
    @include('about.vision-mission')
    @include('about.why-choose')
    @include('about.production-process')
    @include('about.cta')
@endsection