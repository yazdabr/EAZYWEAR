@extends('layouts.website')

@section('title', 'Contact Eazywear Indonesia | Custom Sportswear')

@section('meta_description', 'Contact Eazywear Indonesia for custom sportswear, jerseys, teamwear, apparel, and product inquiries.')

@section('canonical', url('/contact'))

@section('og_title', 'Contact Eazywear Indonesia')

@section('og_description', 'Get in touch with Eazywear Indonesia for custom sportswear, jerseys, teamwear, and apparel.')

@section('og_url', url('/contact'))

@section('content')

    @include('contact.hero')

    @include('contact.contact-info')

    @include('contact.maps')

    @include('contact.cta')
@endsection