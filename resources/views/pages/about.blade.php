@extends('layouts.website')

@section('title', 'About Eazywear Indonesia | Our Story')

@section('meta_description', 'Learn more about Eazywear Indonesia, our story, craftsmanship, innovation, and commitment to creating quality sportswear for teams, communities, schools, and businesses.')

@section('canonical', url('/about'))

@section('og_title', 'About Eazywear Indonesia')

@section('og_description', 'Discover the story behind Eazywear Indonesia and our commitment to quality sportswear.')

@section('og_url', url('/about'))

@section('content')

    @include('about.hero')

    @include('about.story')

    @include('about.vision-mission')

    @include('about.why-choose')

    @include('about.production-process')

    @include('about.cta')

@endsection