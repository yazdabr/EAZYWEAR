@extends('layouts.website')

@section('title', 'About')

@section('content')

    @include('about.hero')

    @include('about.story')

    @include('about.vision-mission')

    @include('about.why-choose')

    @include('about.production-process')

    @include('about.cta')

@endsection