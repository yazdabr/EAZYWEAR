@extends('layouts.website')

@section('title','Detail Pesanan - Eazywear Indonesia')

@section('content')

<section class="bg-gray-50 py-10">
    <x-ui.container>
        <div class="mx-auto max-w-3xl">
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase text-gray-400">Invoice</p>
                        <h1 class="text-2xl font-bold text-slate-900">{{ $transaction->invoice_number }}</h1>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                        {{ $transaction->status }}
                    </span>
                </div>

                <hr class="my-6">

                <h2 class="font-bold text-slate-900">Status Pesanan</h2>

                <div class="mt-5 space-y-5">
                    @forelse($transaction->orderStatusHistories as $history)
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="h-3 w-3 rounded-full bg-[#AE7C18]"></div>
                                @if(!$loop->last)
                                    <div class="h-full w-px bg-gray-200"></div>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">{{ $history->status }}</p>
                                @if($history->note)
                                    <p class="text-sm text-gray-500">{{ $history->note }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-400">{{ $history->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="mt-4 space-y-4">
                            @forelse($transaction->orderStatusHistories as $history)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#AE7C18] text-white">
                                            ✓
                                        </div>

                                        @if(!$loop->last)
                                            <div class="h-full w-px bg-gray-200"></div>
                                        @endif
                                    </div>

                                    <div class="pb-4">
                                        <p class="font-semibold text-slate-900">
                                            @switch($history->status)
                                                @case('PAYMENT_CONFIRMED')
                                                    Pembayaran Berhasil
                                                    @break

                                                @case('ORDER_COMPLETED')
                                                    Pesanan Selesai
                                                    @break

                                                @case('ORDER_CANCELLED')
                                                    Pesanan Dibatalkan
                                                    @break

                                                @case('ORDER_EXPIRED')
                                                    Pembayaran Kadaluarsa
                                                    @break

                                                @default
                                                    Pesanan Dibuat
                                            @endswitch
                                        </p>

                                        <p class="text-sm text-gray-500">
                                            {{ $history->note }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ $history->created_at->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">
                                    Belum ada pembaruan status pesanan.
                                </div>
                            @endforelse
                        </div>
                    @endforelse
                </div>

                <hr class="my-6">

                <h2 class="font-bold text-slate-900">Produk</h2>

                <div class="mt-4 space-y-3">
                    @foreach($transaction->items as $item)
                        <div class="flex justify-between border-b pb-3">
                            <div>
                                <p class="font-semibold">
                                    {{ $item->productVariant?->product?->name ?? '-' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Ukuran: {{ $item->productVariant?->size?->name ?? '-' }}
                                    <br>
                                    Jumlah: {{ $item->qty }}
                                </p>

                                @if($item->custom_name)
                                    <p class="text-sm font-semibold text-[#AE7C18]">
                                        Nama Jersey: {{ $item->custom_name }}
                                    </p>
                                @endif

                                @if($item->custom_number)
                                    <p class="text-sm font-bold">
                                        Nomor: {{ $item->custom_number }}
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
                    <span>Total</span>
                    <strong class="text-[#AE7C18]">
                        Rp {{ number_format($transaction->total,0,',','.') }}
                    </strong>
                </div>
            </div>
        </div>
    </x-ui.container>
</section>

@endsection