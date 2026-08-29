@extends('layouts.website')

@section('title', 'Eazywear Indonesia | Custom Sportswear & Jersey')
@section('meta_description', 'Eazywear Indonesia menyediakan custom sportswear, jersey, teamwear, dan apparel berkualitas untuk tim, komunitas, sekolah, dan bisnis.')

@section('canonical', rtrim(config('app.url'), '/') . '/')

@section('og_title', 'Eazywear Indonesia | Custom Sportswear & Jersey')
@section('og_description', 'Custom sportswear, jersey, teamwear, dan apparel berkualitas untuk tim, komunitas, sekolah, dan bisnis.')
@section('og_url', rtrim(config('app.url'), '/') . '/')

@section('content')
    @include('home.hero')
    @include('home.why-choose')
    @include('home.categories')
    @include('home.featured-products')
    @include('home.production-process')
    @include('home.testimonials')
    @include('home.cta')
@endsection