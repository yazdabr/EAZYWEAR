@extends('admin.layouts.app')

@section('title', 'Detail Transaksi')

@section('page-title', 'Detail Transaksi')

@section('content')

@php
$statusMap = [

    'ORDER_CREATED' => [
        'label' => 'Pesanan Dibuat',
        'color' => 'bg-blue-100 text-blue-700',
        'icon' => 'shopping-cart',
    ],

    'PAYMENT_CONFIRMED' => [
        'label' => 'Pembayaran Dikonfirmasi',
        'color' => 'bg-emerald-100 text-emerald-700',
        'icon' => 'credit-card',
    ],

    'ORDER_PROCESSING' => [
        'label' => 'Pesanan Diproses',
        'color' => 'bg-amber-100 text-amber-700',
        'icon' => 'cog',
    ],

    'ORDER_SHIPPED' => [
        'label' => 'Pesanan Dikirim',
        'color' => 'bg-indigo-100 text-indigo-700',
        'icon' => 'truck',
    ],

    'ORDER_COMPLETED' => [
        'label' => 'Pesanan Selesai',
        'color' => 'bg-green-100 text-green-700',
        'icon' => 'check-circle',
    ],

    'ORDER_CANCELLED' => [
        'label' => 'Pesanan Dibatalkan',
        'color' => 'bg-red-100 text-red-700',
        'icon' => 'x-circle',
    ],

];

$latestHistory = $transaction->orderStatusHistories->sortByDesc('created_at')->first();

$currentStatus = $statusMap[$latestHistory?->status ?? ''] ?? [
    'label' => $transaction->status,
    'color' => 'bg-slate-100 text-slate-700',
    'icon' => 'clock',
];
@endphp


<div class="space-y-6">


{{-- HEADER --}}
<div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">

    <div>
        <div class="flex items-center gap-3">

            <h1 class="text-xl font-bold text-slate-900">
                {{ $transaction->invoice_number }}
            </h1>

            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $currentStatus['color'] }}">
                {{ $currentStatus['label'] }}
            </span>

        </div>

        <p class="mt-2 text-sm text-slate-500">
            {{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->format('d M Y H:i') }}
        </p>
    </div>


    <div class="flex gap-3">

        <a href="{{ route('admin.transactions') }}"
           class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">

            Kembali

        </a>


        <a href="{{ route('admin.transactions.print',$transaction->invoice_number) }}"
           target="_blank"
           class="inline-flex items-center rounded-xl bg-[#AE7C18] px-4 py-2 text-sm font-semibold text-white hover:bg-[#96690F]">

            Cetak Invoice

        </a>

    </div>

</div>



<div class="grid gap-6 lg:grid-cols-3">


{{-- LEFT --}}
<div class="space-y-6 lg:col-span-2">


{{-- CUSTOMER --}}
<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">


<div class="mb-5 flex items-center gap-3">

<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
<x-heroicon-o-user class="h-5 w-5"/>
</div>

<h2 class="font-bold text-slate-900">
Informasi Pelanggan
</h2>

</div>



<div class="grid gap-4 sm:grid-cols-2">

<div>
<p class="text-xs uppercase text-slate-400">
Nama
</p>

<p class="mt-1 font-semibold text-slate-900">
{{ $transaction->shipping_name ?? $transaction->customer?->name }}
</p>
</div>


<div>
<p class="text-xs uppercase text-slate-400">
Telepon
</p>

<p class="mt-1 font-semibold text-slate-900">
{{ $transaction->shipping_phone ?? '-' }}
</p>
</div>


<div>
<p class="text-xs uppercase text-slate-400">
Email
</p>

<p class="mt-1 font-semibold text-slate-900">
{{ $transaction->shipping_email ?? '-' }}
</p>
</div>


<div class="sm:col-span-2">

<p class="text-xs uppercase text-slate-400">
Alamat Pengiriman
</p>

<p class="mt-1 leading-6 font-semibold text-slate-900">

{{ $transaction->shipping_address }}

</p>


<p class="mt-1 text-sm text-slate-500">

{{ $transaction->shipping_district }},
{{ $transaction->shipping_city }},
{{ $transaction->shipping_province }}
{{ $transaction->shipping_postal_code }}

</p>

</div>


</div>

</div>




{{-- ITEMS --}}
<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">


<div class="mb-5 flex items-center gap-3">

<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
<x-heroicon-o-shopping-bag class="h-5 w-5"/>
</div>

<h2 class="font-bold text-slate-900">
Produk
</h2>

</div>



<div class="space-y-4">


@foreach($transaction->items as $item)

@php
$product = $item->productVariant?->product;
@endphp


<div class="flex gap-4 rounded-2xl bg-slate-50 p-4">


<div class="h-16 w-16 overflow-hidden rounded-xl bg-slate-200">

@if($product?->images?->first())

<img
src="{{ asset('storage/'.$product->images->first()->image) }}"
class="h-full w-full object-cover">

@else

<div class="flex h-full items-center justify-center text-xs text-slate-400">
No Image
</div>

@endif

</div>



<div class="flex-1">

<p class="font-bold text-slate-900">

{{ $product?->name ?? '-' }}

</p>


<p class="mt-1 text-sm text-slate-500">

Ukuran:
{{ $item->productVariant?->size?->name ?? '-' }}

</p>


@if($item->custom_name)

<p class="text-sm text-[#AE7C18]">
Nama:
{{ $item->custom_name }}
</p>

@endif


@if($item->custom_number)

<p class="text-sm text-slate-600">
Nomor:
{{ $item->custom_number }}
</p>

@endif


</div>


<div class="text-right">

<p class="font-bold text-slate-900">

Rp {{ number_format($item->subtotal,0,',','.') }}

</p>


<p class="text-xs text-slate-400">

{{ $item->qty }} pcs

</p>

</div>


</div>


@endforeach


</div>


</div>


</div>




{{-- RIGHT --}}
<div class="space-y-6">


{{-- PAYMENT --}}
<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">


<h2 class="mb-4 font-bold text-slate-900">
Pembayaran
</h2>


<div class="space-y-3 text-sm">


<div class="flex justify-between">

<span class="text-slate-500">
Metode
</span>

<span class="font-bold">
{{ $transaction->payment_method }}
</span>

</div>



<div class="flex justify-between">

<span class="text-slate-500">
Status
</span>

<span>
{{ $transaction->status }}
</span>

</div>


</div>

</div>



{{-- SUMMARY --}}
<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">


<h2 class="mb-4 font-bold text-slate-900">
Ringkasan
</h2>


<div class="space-y-3 text-sm">


<div class="flex justify-between">
<span>Subtotal</span>
<span>
Rp {{ number_format($transaction->subtotal,0,',','.') }}
</span>
</div>


<div class="flex justify-between">
<span>Diskon</span>
<span>
Rp {{ number_format($transaction->discount,0,',','.') }}
</span>
</div>


<div class="flex justify-between">
<span>Pengiriman</span>
<span>
Rp {{ number_format($transaction->shipping,0,',','.') }}
</span>
</div>


<div class="border-t pt-3 flex justify-between text-base font-bold">

<span>Total</span>

<span class="text-[#AE7C18]">

Rp {{ number_format($transaction->total,0,',','.') }}

</span>

</div>


</div>


</div>




{{-- TIMELINE --}}
<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">


<h2 class="mb-6 font-bold text-slate-900">
Timeline Status
</h2>


<div class="space-y-6">


@foreach($transaction->orderStatusHistories->sortBy('created_at') as $history)

@php
$status = $statusMap[$history->status] ?? null;
@endphp


<div class="flex gap-4">


<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">

    @switch($status['icon'] ?? null)

        @case('shopping-cart')
            <x-heroicon-o-shopping-cart class="h-5 w-5"/>
            @break

        @case('credit-card')
            <x-heroicon-o-credit-card class="h-5 w-5"/>
            @break

        @case('cog')
            <x-heroicon-o-cog-6-tooth class="h-5 w-5"/>
            @break

        @case('truck')
            <x-heroicon-o-truck class="h-5 w-5"/>
            @break

        @case('check-circle')
            <x-heroicon-o-check-circle class="h-5 w-5"/>
            @break

        @case('x-circle')
            <x-heroicon-o-x-circle class="h-5 w-5"/>
            @break

        @default
            <x-heroicon-o-clock class="h-5 w-5"/>

    @endswitch

</div>


<div>

<p class="font-bold text-slate-900">

{{ $status['label'] ?? $history->status }}

</p>


<p class="text-sm text-slate-500">

{{ $history->note }}

</p>


<p class="mt-1 text-xs text-slate-400">

{{ $history->created_at->format('d M Y H:i') }}

</p>


</div>


</div>


@endforeach


</div>


</div>


</div>


</div>


</div>

@endsection
