@extends('layouts.website')

@section('title','Detail Pesanan - Eazywear Indonesia')

@section('content')

<section class="bg-gray-50 py-10">

<x-ui.container>

<div class="mx-auto max-w-3xl">


<div class="rounded-2xl border bg-white p-6 shadow-sm">


<div class="flex items-center justify-between">

<div>

<p class="text-xs uppercase text-gray-400">
Invoice
</p>

<h1 class="text-2xl font-bold text-slate-900">
{{ $transaction->invoice_number }}
</h1>

</div>


<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
{{ $transaction->status }}
</span>


</div>



<hr class="my-6">


<h2 class="font-bold text-slate-900">
Produk
</h2>


<div class="mt-4 space-y-3">


@foreach($transaction->items as $item)

<div class="flex justify-between border-b pb-3">


<div>

<p class="font-semibold">
{{ $item->productVariant?->product?->name ?? '-' }}
</p>


<p class="text-sm text-gray-500">
Ukuran:
{{ $item->productVariant?->size?->name ?? '-' }}

<br>

Jumlah:
{{ $item->qty }}

</p>


@if($item->custom_name)

<p class="text-sm font-bold text-[#AE7C18]">
Nama:
{{ $item->custom_name }}
</p>

@endif


@if($item->custom_number)

<p class="text-sm font-bold">
Nomor:
{{ $item->custom_number }}
</p>

@endif


</div>


<strong>
Rp {{ number_format($item->subtotal,0,',','.') }}
</strong>


</div>


@endforeach


</div>


<hr class="my-6">


<div class="flex justify-between">

<span>
Total
</span>

<strong class="text-[#AE7C18]">
Rp {{ number_format($transaction->total,0,',','.') }}
</strong>

</div>


</div>


</div>

</x-ui.container>

</section>


@endsection