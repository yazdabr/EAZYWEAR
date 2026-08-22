<section class="border-b bg-white py-4">
    <x-ui.container>
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a
                href="{{ route('home') }}"
                class="transition hover:text-[#AE7C18]"
            >
                Home
            </a>

            <span>/</span>

            <a
                href="{{ route('catalog') }}"
                class="transition hover:text-[#AE7C18]"
            >
                Catalog
            </a>

            <span>/</span>

            <span class="font-medium text-[#AE7C18]">
                {{ $product->name }}
            </span>
        </nav>
    </x-ui.container>
</section>