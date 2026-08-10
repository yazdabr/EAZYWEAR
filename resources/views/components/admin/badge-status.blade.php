@props([
    'status'
])

@php

$status = trim($status);

$classes = match ($status) {

    'Aktif' =>
        'bg-emerald-100 text-emerald-700',

    'Tidak Aktif' =>
        'bg-red-100 text-red-700',

    'Menunggu' =>
        'bg-amber-100 text-amber-700',

    'Lunas' =>
        'bg-sky-100 text-sky-700',

    'Selesai' =>
        'bg-emerald-100 text-emerald-700',

    'Dibatalkan' =>
        'bg-red-100 text-red-700',

    default =>
        'bg-slate-100 text-slate-700',

};

@endphp

<span
    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">

    {{ $status }}

</span>