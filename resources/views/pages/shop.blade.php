@extends('layouts.website')

@section('title', 'Shop | Eazywear Indonesia')
@section('meta_description', 'Explore the Eazywear Indonesia shop for custom sportswear, jerseys, teamwear, and apparel designed for teams, communities, schools, and businesses.')

@section('canonical', rtrim(config('app.url'), '/') . '/shop')

@section('og_title', 'Shop | Eazywear Indonesia')
@section('og_description', 'Explore Eazywear Indonesia custom sportswear, jerseys, teamwear, and apparel.')
@section('og_url', rtrim(config('app.url'), '/') . '/shop')

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
                'name' => 'Shop',
                'item' => $siteUrl . '/shop',
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div x-data="quickView" class="bg-white">
    @include('shop.hero')
    @include('shop.search-filter')
    @include('shop.product-grid')

    @if($products->count() > 0)
        @include('shop.pagination')
    @endif

    @include('shop.quick-view-modal')
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('quickView', () => ({
        open: false,
        productId: null,
        productUrl: '#',
        title: '',
        series: '',
        image: '',
        price: '',
        description: '',
        preOrderClosed: false,
        readyStockClosed: false,
        isDragging: false,
        offsetY: 0,
        startY: 0,

        show(product) {
            this.productId = product.id ?? null;
            this.productUrl = product.url ?? '#';
            this.title = product.title ?? '';
            this.series = product.series ?? '';
            this.image = product.image ?? '';
            this.price = product.price ?? '';
            this.description = product.description ?? '';
            this.preOrderClosed = product.preOrderClosed === true || product.preOrderClosed === 1 || product.preOrderClosed === '1';
            this.readyStockClosed = product.readyStockClosed === true || product.readyStockClosed === 1 || product.readyStockClosed === '1';
            this.offsetY = 0;
            this.isDragging = false;
            this.open = true;
        },

        close() {
            this.open = false;
            this.offsetY = 0;
            this.isDragging = false;
            this.startY = 0;
        },

        startDrag(event) {
            this.isDragging = true;
            this.startY = event.touches
                ? event.touches[0].clientY
                : event.clientY;
        },

        onDrag(event) {
            if (!this.isDragging) return;

            const currentY = event.touches
                ? event.touches[0].clientY
                : event.clientY;

            const distance = currentY - this.startY;

            if (distance > 0) {
                this.offsetY = distance;
            }
        },

        endDrag() {
            if (!this.isDragging) return;

            this.isDragging = false;

            if (this.offsetY > 120) {
                this.close();
            } else {
                this.offsetY = 0;
            }
        }
    }));
});
</script>
@endpush