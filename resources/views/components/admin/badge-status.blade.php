@props([

    'status' => 'Active',

])

@php

$status = strtolower($status);

$styles = [

    'active' => [

        'bg' => 'bg-emerald-100',

        'text' => 'text-emerald-700',

        'dot' => 'bg-emerald-500',

        'label' => 'Active',

    ],

    'draft' => [

        'bg' => 'bg-amber-100',

        'text' => 'text-amber-700',

        'dot' => 'bg-amber-500',

        'label' => 'Draft',

    ],

    'low stock' => [

        'bg' => 'bg-orange-100',

        'text' => 'text-orange-700',

        'dot' => 'bg-orange-500',

        'label' => 'Low Stock',

    ],

    'out of stock' => [

        'bg' => 'bg-red-100',

        'text' => 'text-red-700',

        'dot' => 'bg-red-500',

        'label' => 'Out of Stock',

    ],

    'archived' => [

        'bg' => 'bg-slate-200',

        'text' => 'text-slate-700',

        'dot' => 'bg-slate-500',

        'label' => 'Archived',

    ],

];

$current = $styles[$status] ?? $styles['active'];

@endphp

<span
    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $current['bg'] }} {{ $current['text'] }}">

    <span
        class="h-2 w-2 rounded-full {{ $current['dot'] }}">

    </span>

    {{ $current['label'] }}

</span>