@extends('layouts.website')

@section('title', 'Cek Pesanan - Eazywear Indonesia')

@section('content')

<section class="min-h-[70vh] bg-gray-50 py-10">

    <x-ui.container>

        <div class="mx-auto max-w-xl">

            <div class="rounded-2xl border bg-white p-6 shadow-sm">

                <div class="text-center">

                    <h1 class="text-2xl font-bold text-slate-900">
                        Cek Pesanan
                    </h1>

                    <p class="mt-2 text-sm text-gray-500">
                        Masukkan nomor invoice dan email saat checkout.
                    </p>

                </div>


                @if(session('error'))

                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>

                @endif



                <form
                    method="POST"
                    action="{{ route('orders.tracking.search') }}"
                    class="mt-6 space-y-4"
                >

                    @csrf


                    <div>

                        <label class="text-sm font-semibold text-slate-700">
                            Nomor Invoice
                        </label>

                        <input
                            type="text"
                            name="invoice_number"
                            value="{{ old('invoice_number') }}"
                            placeholder="INV-20260925-XXXXXX"
                            class="mt-1 w-full rounded-xl border-gray-300"
                            required
                        >

                    </div>


                    <div>

                        <label class="text-sm font-semibold text-slate-700">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="email@email.com"
                            class="mt-1 w-full rounded-xl border-gray-300"
                            required
                        >

                    </div>


                    <button
                        class="w-full rounded-xl bg-[#AE7C18] py-3 text-sm font-bold text-white hover:bg-[#8F6514]"
                    >
                        Cek Pesanan
                    </button>


                </form>

            </div>

        </div>

    </x-ui.container>

</section>

@endsection