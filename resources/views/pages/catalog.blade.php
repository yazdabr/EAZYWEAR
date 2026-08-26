@extends('layouts.website')

@section('title', 'Catalog')

@section('content')
<div>
    @include('catalog.hero')
    @include('catalog.search-filter')
    @include('catalog.product-grid')
    @include('catalog.pagination')
    @include('catalog.quick-view-modal')
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
        isDragging: false,
        offsetY: 0,
        startY: 0,

        show(product) {
            this.productId = product.id ?? null;.0
            this.productUrl = product.url ?? '#';
            this.title = product.title ?? '';
            this.series = product.series ?? '';
            this.image = product.image ?? '';
            this.price = product.price ?? '';
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