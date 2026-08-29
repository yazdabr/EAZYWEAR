@extends('layouts.website')

@section('title', 'Contact Eazywear Indonesia | Custom Sportswear')
@section('meta_description', 'Contact Eazywear Indonesia for custom sportswear, jerseys, teamwear, apparel, and product inquiries.')

@section('canonical', rtrim(config('app.url'), '/') . '/contact')

@section('og_title', 'Contact Eazywear Indonesia')
@section('og_description', 'Get in touch with Eazywear Indonesia for custom sportswear, jerseys, teamwear, and apparel.')
@section('og_url', rtrim(config('app.url'), '/') . '/contact')

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
                'name' => 'Contact',
                'item' => $siteUrl . '/contact',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
    @include('contact.hero')
    @include('contact.contact-info')
    @include('contact.maps')
    @include('contact.cta')
@endsection