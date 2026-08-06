@extends('layouts.website')

@section('title', 'Catalog')

@section('content')

<div
    x-data="quickView">

    @include('catalog.hero')

    @include('catalog.search-filter')

    @include('catalog.product-grid')

    @include('catalog.pagination')

    @include('catalog.quick-view-modal')

</div>

@endsection

<script>

function quickView(){

    return{

        open:false,

        title:'',

        series:'',

        image:'',

        price:'',

        show(product){

            this.title=product.title;

            this.series=product.series;

            this.image=product.image;

            this.price=product.price;

            this.open=true;

        },

        close(){

            this.open=false;

        }

    }

}

</script>