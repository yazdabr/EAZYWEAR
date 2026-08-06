@props([
    'animation' => 'up',
    'index' => null,
    'delay' => null,
    'duration' => 700,
])

@php

    if (is_null($delay)) {
        $delay = !is_null($index)
            ? $index * 120
            : 0;
    }

    $hiddenClass = match ($animation) {

        'left'  => 'reveal-left',

        'right' => 'reveal-right',

        'down'  => 'reveal-down',

        'scale' => 'reveal-scale',

        default => 'reveal-up',

    };

@endphp

<div

    x-data="{ show: false }"

    x-intersect.once="
        setTimeout(() => {
            show = true
        }, {{ $delay }})
    "

    :class="show ? 'revealed' : '{{ $hiddenClass }}'"

    style="transition-duration: {{ $duration }}ms;"

    {{ $attributes }}

>

    {{ $slot }}

</div>