@extends('layouts.website')

@section('title', 'Contact')

@section('content')

    @include('contact.hero')

    @include('contact.contact-info')

    @include('contact.maps')

    @include('contact.cta')
@endsection