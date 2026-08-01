@props([
    'title',
    'image',
    'href' => '#'
])

<a
    href="{{ $href }}"
    class="group block overflow-hidden rounded-3xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">

    <div class="aspect-[4/5] overflow-hidden">

        <img
            src="{{ asset($image) }}"
            alt="{{ $title }}"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-110">

    </div>

    <div class="flex items-center justify-between p-6">

        <h3 class="text-xl font-bold text-gray-900">

            {{ $title }}

        </h3>

        <div
            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18] text-white transition group-hover:rotate-45">

            <x-heroicon-o-arrow-up-right class="h-5 w-5"/>

        </div>

    </div>

</a>