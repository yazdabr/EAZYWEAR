@extends('layouts.website')

@section('title', $product->name)

@section('content')

    @include('product-detail.breadcrumb')

    @include('product-detail.product-info')

    @include('product-detail.technologies')

@endsection