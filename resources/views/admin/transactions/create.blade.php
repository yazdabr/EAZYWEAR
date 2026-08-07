@extends('admin.layouts.app')

@section('title', 'New Transaction')

@section('page-title', 'New Transaction')

@section('content')

<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>

            <h2 class="text-3xl font-bold text-slate-900">

                New Transaction

            </h2>

            <p class="mt-2 text-slate-500">

                Create a manual transaction for walk-in customers.

            </p>

        </div>

        <a

            href="{{ route('admin.transactions') }}"

            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

            <x-heroicon-o-arrow-left class="h-5 w-5"/>

            Back

        </a>

    </div>

    {{-- ================= CUSTOMER INFORMATION ================= --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5">

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#AE7C18]/10">

                <x-heroicon-o-user
                    class="h-6 w-6 text-[#AE7C18]" />

            </div>

            <div>

                <h3 class="text-lg font-bold text-slate-900">

                    Customer Information

                </h3>

                <p class="mt-1 text-sm text-slate-500">

                    Fill customer details before creating the transaction.

                </p>

            </div>

        </div>

        {{-- Body --}}
        <div class="p-6">

            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Customer Name --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Customer Name

                        <span class="text-red-500">*</span>

                    </label>

                    <x-admin.input
                        placeholder="Enter customer name..." />

                </div>

                {{-- Phone --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Phone Number

                    </label>

                    <x-admin.input
                        placeholder="08xxxxxxxxxx" />

                </div>

                {{-- Email --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Email

                    </label>

                    <x-admin.input
                        type="email"
                        placeholder="customer@email.com" />

                </div>

                {{-- Transaction Date --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Transaction Date

                    </label>

                    <input

                        type="date"

                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">

                </div>

            </div>

            {{-- Notes --}}
            <div class="mt-6">

                <label class="mb-2 block text-sm font-medium text-slate-700">

                    Notes

                </label>

                <x-admin.textarea

                    rows="4"

                    placeholder="Additional notes for this transaction..." />

            </div>

        </div>

    </div>

    {{-- ================= PRODUCT SELECTION ================= --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5">

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#AE7C18]/10">

                <x-heroicon-o-cube
                    class="h-6 w-6 text-[#AE7C18]" />

            </div>

            <div>

                <h3 class="text-lg font-bold text-slate-900">

                    Product Selection

                </h3>

                <p class="mt-1 text-sm text-slate-500">

                    Search and add products to this transaction.

                </p>

            </div>

        </div>

        {{-- Body --}}
        <div class="p-6">

            <div class="grid gap-5 xl:grid-cols-12">

                {{-- Product --}}
                <div class="xl:col-span-6">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Product

                    </label>

                    <x-admin.search-input
                        placeholder="Search product..." />

                </div>

                {{-- Size --}}
                <div class="xl:col-span-2">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Size

                    </label>

                    <x-admin.select>

                        <option>XS</option>
                        <option>S</option>
                        <option>M</option>
                        <option>L</option>
                        <option>XL</option>

                    </x-admin.select>

                </div>

                {{-- Qty --}}
                <div class="xl:col-span-2">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Qty

                    </label>

                    <input

                        type="number"

                        min="1"

                        value="1"

                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">

                </div>

                {{-- Button --}}
                <div class="flex items-end xl:col-span-2">

                    <button

                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F]">

                        <x-heroicon-o-plus
                            class="h-5 w-5"/>

                        Add Item

                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- ================= SHOPPING CART ================= --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

            <div class="flex items-center gap-4">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#AE7C18]/10">

                    <x-heroicon-o-shopping-cart
                        class="h-6 w-6 text-[#AE7C18]" />

                </div>

                <div>

                    <h3 class="text-lg font-bold text-slate-900">

                        Shopping Cart

                    </h3>

                    <p class="mt-1 text-sm text-slate-500">

                        Products added to this transaction.

                    </p>

                </div>

            </div>

            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">

                2 Items

            </span>

        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">

                        <th class="px-6 py-4">

                            Product

                        </th>

                        <th class="px-6 py-4 text-center">

                            Size

                        </th>

                        <th class="px-6 py-4 text-center">

                            Qty

                        </th>

                        <th class="px-6 py-4 text-right">

                            Price

                        </th>

                        <th class="px-6 py-4 text-right">

                            Total

                        </th>

                        <th class="px-6 py-4 text-center">

                            Action

                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-200">

                    <tr class="hover:bg-slate-50 transition">

                        <td class="px-6 py-5">

                            <div>

                                <p class="font-semibold text-slate-900">

                                    Apex Pro Jersey

                                </p>

                                <p class="mt-1 text-sm text-slate-500">

                                    SKU : PRD-001

                                </p>

                            </div>

                        </td>

                        <td class="px-6 py-5 text-center">

                            XL

                        </td>

                        <td class="px-6 py-5 text-center">

                            2

                        </td>

                        <td class="px-6 py-5 text-right">

                            Rp149.000

                        </td>

                        <td class="px-6 py-5 text-right font-bold text-[#AE7C18]">

                            Rp298.000

                        </td>

                        <td class="px-6 py-5 text-center">

                            <button
                                class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">

                                <x-heroicon-o-trash
                                    class="h-5 w-5"/>

                            </button>

                        </td>

                    </tr>

                    <tr class="hover:bg-slate-50 transition">

                        <td class="px-6 py-5">

                            <div>

                                <p class="font-semibold text-slate-900">

                                    Elite Jersey

                                </p>

                                <p class="mt-1 text-sm text-slate-500">

                                    SKU : PRD-002

                                </p>

                            </div>

                        </td>

                        <td class="px-6 py-5 text-center">

                            M

                        </td>

                        <td class="px-6 py-5 text-center">

                            1

                        </td>

                        <td class="px-6 py-5 text-right">

                            Rp199.000

                        </td>

                        <td class="px-6 py-5 text-right font-bold text-[#AE7C18]">

                            Rp199.000

                        </td>

                        <td class="px-6 py-5 text-center">

                            <button
                                class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">

                                <x-heroicon-o-trash
                                    class="h-5 w-5"/>

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-5">

            <div class="text-sm text-slate-500">

                Total Products :
                <span class="font-semibold text-slate-700">

                    2

                </span>

            </div>

            <div class="text-lg font-bold text-[#AE7C18]">

                Grand Total : Rp497.000

            </div>

        </div>

    </div>

    {{-- ================= PAYMENT ================= --}}
    <div class="grid gap-6 xl:grid-cols-2">

        {{-- Payment Method --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#AE7C18]/10">

                    <x-heroicon-o-credit-card
                        class="h-6 w-6 text-[#AE7C18]" />

                </div>

                <div>

                    <h3 class="text-lg font-bold text-slate-900">

                        Payment Method

                    </h3>

                    <p class="mt-1 text-sm text-slate-500">

                        Choose how the customer pays.

                    </p>

                </div>

            </div>

            <div class="space-y-5 p-6">

                {{-- Cash --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5">

                    <input
                        type="radio"
                        name="payment"
                        checked
                        class="text-[#AE7C18] focus:ring-[#AE7C18]">

                    <div>

                        <p class="font-semibold text-slate-900">

                            Cash

                        </p>

                        <p class="text-sm text-slate-500">

                            Pay using cash.

                        </p>

                    </div>

                </label>

                {{-- QRIS --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5">

                    <input
                        type="radio"
                        name="payment"
                        class="text-[#AE7C18] focus:ring-[#AE7C18]">

                    <div>

                        <p class="font-semibold text-slate-900">

                            QRIS

                        </p>

                        <p class="text-sm text-slate-500">

                            Scan QR code.

                        </p>

                    </div>

                </label>

                {{-- Transfer --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5">

                    <input
                        type="radio"
                        name="payment"
                        class="text-[#AE7C18] focus:ring-[#AE7C18]">

                    <div>

                        <p class="font-semibold text-slate-900">

                            Bank Transfer

                        </p>

                        <p class="text-sm text-slate-500">

                            Bank account transfer.

                        </p>

                    </div>

                </label>

                {{-- EDC --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5">

                    <input
                        type="radio"
                        name="payment"
                        class="text-[#AE7C18] focus:ring-[#AE7C18]">

                    <div>

                        <p class="font-semibold text-slate-900">

                            EDC

                        </p>

                        <p class="text-sm text-slate-500">

                            Debit / Credit Card.

                        </p>

                    </div>

                </label>

                {{-- Amount Paid --}}
                <div class="pt-3">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Amount Paid

                    </label>

                    <input

                        type="number"

                        placeholder="0"

                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">

                </div>

                {{-- Change --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Change

                    </label>

                    <input

                        type="text"

                        readonly

                        value="Rp 0"

                        class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 font-semibold text-slate-700">

                </div>

            </div>

        </div>

        {{-- ================= ORDER SUMMARY ================= --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

            {{-- Header --}}
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">

                    <x-heroicon-o-document-text
                        class="h-6 w-6 text-emerald-600" />

                </div>

                <div>

                    <h3 class="text-lg font-bold text-slate-900">

                        Order Summary

                    </h3>

                    <p class="mt-1 text-sm text-slate-500">

                        Review the transaction before saving.

                    </p>

                </div>

            </div>

            {{-- Body --}}
            <div class="space-y-5 p-6">

                {{-- Subtotal --}}
                <div class="flex items-center justify-between">

                    <span class="text-slate-600">

                        Subtotal

                    </span>

                    <span class="font-semibold text-slate-900">

                        Rp497.000

                    </span>

                </div>

                {{-- Discount --}}
                <div class="flex items-center justify-between">

                    <span class="text-slate-600">

                        Discount

                    </span>

                    <span class="font-semibold text-slate-900">

                        Rp0

                    </span>

                </div>

                {{-- Tax --}}
                <div class="flex items-center justify-between">

                    <span class="text-slate-600">

                        Tax

                    </span>

                    <span class="font-semibold text-slate-900">

                        Rp0

                    </span>

                </div>

                {{-- Shipping --}}
                <div class="flex items-center justify-between">

                    <span class="text-slate-600">

                        Shipping

                    </span>

                    <span class="font-semibold text-slate-900">

                        Rp20.000

                    </span>

                </div>

                <div class="border-t border-dashed border-slate-300"></div>

                {{-- Grand Total --}}
                <div class="flex items-center justify-between">

                    <span class="text-lg font-bold text-slate-900">

                        Grand Total

                    </span>

                    <span class="text-2xl font-bold text-[#AE7C18]">

                        Rp517.000

                    </span>

                </div>

            </div>

        </div>

    </div>

    {{-- ================= ACTION BUTTON ================= --}}
    <div class="flex flex-col-reverse gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">

        {{-- Back --}}
        <a

            href="{{ route('admin.transactions') }}"

            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

            <x-heroicon-o-arrow-left
                class="h-5 w-5"/>

            Back to Transactions

        </a>

        <div class="flex gap-3">

            {{-- Reset --}}
            <button

                type="reset"

                class="rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                Reset

            </button>

            {{-- Save --}}
            <button

                @click="

                    $dispatch('toast',{

                        type:'success',

                        title:'Transaction Created',

                        message:'New transaction has been saved successfully.'

                    });

                    setTimeout(()=>{

                        window.location='{{ route('admin.transactions') }}';

                    },900);

                "

                class="inline-flex items-center gap-2 rounded-xl bg-[#AE7C18] px-7 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F]">

                <x-heroicon-o-check-circle
                    class="h-5 w-5"/>

                Save Transaction

            </button>

        </div>

    </div>

</div>

@endsection