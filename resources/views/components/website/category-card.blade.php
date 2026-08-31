@props(['title','image','href' => '#'])
<a href="{{ $href }}" class="group block overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-3xl sm:hover:-translate-y-2 sm:hover:shadow-2xl">
    <div class="aspect-[16/10] overflow-hidden sm:aspect-[4/5]">
        <img src="{{ asset($image) }}" alt="{{ $title }}" width="800" height="500" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
    </div>
    <div class="flex items-center justify-between p-4 sm:p-6">
        <h3 class="text-base font-bold text-gray-900 sm:text-xl">{{ $title }}</h3>
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#AE7C18] text-white transition group-hover:rotate-45 sm:h-10 sm:w-10">
            <x-heroicon-o-arrow-up-right class="h-4 w-4 sm:h-5 sm:w-5"/>
        </div>
    </div>
</a>