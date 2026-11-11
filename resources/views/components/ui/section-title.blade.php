@props([
'title',
'subtitle'=>null
])

<div class="mb-12 text-center">

<h2 class="text-4xl font-bold dark:text-[#F8FAFC]">

{{ $title }}

</h2>

@if($subtitle)

<p class="mx-auto mt-4 max-w-2xl text-gray-600 dark:text-[#A1A1AA]">

{{ $subtitle }}

</p>

@endif

</div>