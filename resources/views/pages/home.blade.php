@extends('layouts.website')

@section('title', 'Home')

@section('content')

    @include('home.hero')

    @include('home.why-choose')

    @include('home.categories')

    @include('home.featured-products')

    @include('home.production-process')

    @include('home.testimonials')

    @include('home.cta')

@endsection