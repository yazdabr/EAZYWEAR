@props([
'title',
'subtitle'=>null
])

<div class="mb-12 text-center">

<h2 class="text-4xl font-bold">

{{ $title }}

</h2>

@if($subtitle)

<p class="mx-auto mt-4 max-w-2xl text-gray-600">

{{ $subtitle }}

</p>

@endif

</div>