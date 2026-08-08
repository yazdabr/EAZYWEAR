@extends('admin.layouts.app')

@section('title', 'Transactions')

@section('page-title', 'Transactions')

@section('content')

@php

$transactions = [

    [
        'invoice' => 'INV-20260807-001',
        'date' => '07 Aug 2026',
        'customer' => 'John Doe',
        'total' => 'Rp 450.000',
        'payment' => 'QRIS',
        'status' => 'Paid',
    ],

    [
        'invoice' => 'INV-20260807-002',
        'date' => '07 Aug 2026',
        'customer' => 'Michael Jordan',
        'total' => 'Rp 1.250.000',
        'payment' => 'Cash',
        'status' => 'Completed',
    ],

    [
        'invoice' => 'INV-20260807-003',
        'date' => '06 Aug 2026',
        'customer' => 'Cristiano Ronaldo',
        'total' => 'Rp 299.000',
        'payment' => 'Transfer',
        'status' => 'Pending',
    ],

];

@endphp

<div class="space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

        <div>

            <h2 class="text-3xl font-bold text-slate-900">

                Transactions

            </h2>

            <p class="mt-2 text-slate-500">

                Manage all customer transactions and payments.

            </p>

        </div>

        <a

            href="{{ route('admin.transactions.create') }}"

            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F]">

            <x-heroicon-o-plus
                class="h-5 w-5"/>

            New Transaction

        </a>

    </div>

    {{-- ================= FILTER ================= --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">

        {{-- Header Section Filter --}}
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">

            <div class="flex items-center gap-2.5">
                <h3 class="text-xl font-semibold text-slate-800">

                    Filter Data

                </h3>
            </div>
            <span class="text-xs text-slate-400">

                Filter berdasarkan kata kunci & kriteria

            </span>
        </div>

        {{-- Controls Grid --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-12">

            {{-- ================= SEARCH ================= --}}
            <div class="md:col-span-2 lg:col-span-4">

                <label class="mb-1.5 block text-xs font-medium text-slate-600">

                    Search

                </label>

                <x-admin.search-input
                    placeholder="Search customer name or email..." />

            </div>


            {{-- ================= RIGHT CONTROLS ================= --}}
            <div class="md:col-span-2 lg:col-span-8">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-end">

                    {{-- Month --}}
                    <div class="w-full sm:w-52">

                        <label class="mb-1.5 block text-xs font-medium text-slate-600">

                            Month

                        </label>

                        <x-admin.select>

                            <option value="">

                                All Month

                            </option>

                            <option value="1">

                                January

                            </option>

                            <option value="2">

                                February

                            </option>

                            <option value="3">

                                March

                            </option>

                            <option value="4">

                                April

                            </option>

                            <option value="5">

                                May

                            </option>

                            <option value="6">

                                June

                            </option>

                            <option value="7">

                                July

                            </option>

                            <option value="8">

                                August

                            </option>

                            <option value="9">

                                September

                            </option>

                            <option value="10">

                                October

                            </option>

                            <option value="11">

                                November

                            </option>

                            <option value="12">

                                December

                            </option>

                        </x-admin.select>

                    </div>


                    {{-- Year --}}
                    <div class="w-full sm:w-40">

                        <label class="mb-1.5 block text-xs font-medium text-slate-600">

                            Year

                        </label>

                        <x-admin.select>

                            <option value="">

                                All Year

                            </option>

                            <option value="2026">

                                2026

                            </option>

                            <option value="2025">

                                2025

                            </option>

                            <option value="2024">

                                2024

                            </option>

                        </x-admin.select>

                    </div>


                    {{-- Actions --}}
                    <div class="flex items-center gap-2">

                        {{-- Filter --}}
                        <button

                            type="submit"

                            class="inline-flex h-[50px] items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 text-sm font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98]">

                            <x-heroicon-o-magnifying-glass
                                class="h-4 w-4" />

                            <span>

                                Filter

                            </span>

                        </button>


                        {{-- Reset --}}
                        <button

                            type="reset"

                            title="Reset Filter"

                            class="inline-flex h-[50px] w-[50px] items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98]">

                            <x-heroicon-o-arrow-path
                                class="h-4 w-4" />

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ================= STATISTICS ================= --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        {{-- Total Transactions --}}
        <x-admin.stat-card

            title="Total Transactions"

            value="1,248"

            growth="+12%">

            <x-slot:icon>

                <x-heroicon-o-receipt-percent
                    class="h-7 w-7"/>

            </x-slot:icon>

        </x-admin.stat-card>

        {{-- Total Revenue --}}
        <x-admin.stat-card

            title="Total Revenue"

            value="Rp 245.8M"

            color="bg-emerald-500"

            growth="+18%">

            <x-slot:icon>

                <x-heroicon-o-banknotes
                    class="h-7 w-7"/>

            </x-slot:icon>

        </x-admin.stat-card>

        {{-- Pending Payments --}}
        <x-admin.stat-card

            title="Pending Payments"

            value="23"

            color="bg-amber-500"

            growth="-2">

            <x-slot:icon>

                <x-heroicon-o-clock
                    class="h-7 w-7"/>

            </x-slot:icon>

        </x-admin.stat-card>

        {{-- Completed Orders --}}
        <x-admin.stat-card

            title="Completed Orders"

            value="1,103"

            color="bg-sky-500"

            growth="+9%">

            <x-slot:icon>

                <x-heroicon-o-check-badge
                    class="h-7 w-7"/>

            </x-slot:icon>

        </x-admin.stat-card>

    </div>

    {{-- ================= TRANSACTION TABLE ================= --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">

                        <th class="px-6 py-4">

                            Invoice

                        </th>

                        <th class="px-6 py-4">

                            Date

                        </th>

                        <th class="px-6 py-4">

                            Customer

                        </th>

                        <th class="px-6 py-4 text-center">

                            Total

                        </th>

                        <th class="px-6 py-4 text-center">

                            Payment

                        </th>

                        <th class="px-6 py-4 text-center">

                            Status

                        </th>

                        <th class="px-6 py-4 text-right">

                            Actions

                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-200">

                    @foreach($transactions as $transaction)

                        <x-admin.transaction-row
                            :transaction="$transaction"/>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-4 py-4 text-center sm:px-6 sm:py-5 md:flex-row md:text-left">
            <p class="text-xs font-medium text-slate-500 sm:text-sm">
                Showing <span class="font-semibold text-slate-900">3</span> of <span class="font-semibold text-slate-900">152</span> transactions
            </p>

            <x-admin.pagination />
        </div>

            </div>

        </div>

@endsection

@include('admin.transactions.partials.view-transaction')
@include('admin.transactions.partials.delete-transaction')