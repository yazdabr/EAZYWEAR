@props([
    'status'
])

@php

$status = trim($status);

$classes = match ($status) {

    'Active' =>
        'bg-emerald-100 text-emerald-700',

    'Inactive' =>
        'bg-red-100 text-red-700',

    'Pending' =>
        'bg-amber-100 text-amber-700',

    'Paid' =>
        'bg-sky-100 text-sky-700',

    'Completed' =>
        'bg-emerald-100 text-emerald-700',

    'Cancelled' =>
        'bg-red-100 text-red-700',

    default =>
        'bg-slate-100 text-slate-700',

};

@endphp

<span
    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">

    {{ $status }}

</span>