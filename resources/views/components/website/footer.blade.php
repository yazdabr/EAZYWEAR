<footer class="border-t border-gray-200 bg-white">

    <x-ui.container>

        <div class="grid items-start gap-12 py-20 md:grid-cols-2 lg:grid-cols-4">

            {{-- Brand --}}
            <div class="self-start">

                <img
                    src="{{ asset('images/hero/logoo.png') }}"
                    alt="Eazywear"
                    class="h-5 w-auto">

                <p class="mt-5 max-w-xs leading-8 text-gray-600">

                    Premium Indonesian athletic gear engineered
                    for performance and the technical pursuit of excellence.

                </p>

            </div>

            {{-- Quick Links --}}
            <div>

                <h3
                    class="mb-6 text-sm font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">

                    Quick Links

                </h3>

                <ul class="space-y-4">

                    <li>
                        <a href="/" class="transition hover:text-[#AE7C18]">
                            Home
                        </a>
                    </li>

                    <li>
                        <a href="/catalog" class="transition hover:text-[#AE7C18]">
                            Catalog
                        </a>
                    </li>

                    <li>
                        <a href="/about" class="transition hover:text-[#AE7C18]">
                            About
                        </a>
                    </li>

                    <li>
                        <a href="/contact" class="transition hover:text-[#AE7C18]">
                            Contact
                        </a>
                    </li>

                </ul>

            </div>

            {{-- Categories --}}
            <div>

                <h3
                    class="mb-6 text-sm font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">

                    Categories

                </h3>

                <ul class="space-y-4">

                    <li>Jersey</li>

                    <li>Jacket</li>

                    <li>T-Shirt</li>

                    <li>Pants</li>

                </ul>

            </div>

            {{-- Contact --}}
            <div>

                <h3
                    class="mb-6 text-sm font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">

                    Contact Info

                </h3>

                <div class="space-y-5">

                    <div class="flex items-start gap-3">

                        <x-heroicon-o-map-pin
                            class="mt-1 h-5 w-5 text-[#AE7C18]" />

                        <span>

                            Jakarta Selatan, Indonesia

                        </span>

                    </div>

                    <div class="flex items-center gap-3">

                        <x-heroicon-o-envelope
                            class="h-5 w-5 text-[#AE7C18]" />

                        <span>

                            contact@eazywear.id

                        </span>

                    </div>

                    <div class="flex items-center gap-3">

                        <x-heroicon-o-phone
                            class="h-5 w-5 text-[#AE7C18]" />

                        <span>

                            +62 812 3456 7890

                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- Bottom Footer --}}
        <div
            class="flex flex-col items-center justify-between gap-4 border-t border-gray-200 py-6 text-sm text-gray-500 md:flex-row">

            <p>

                © {{ date('Y') }} Eazywear Indonesia. All rights reserved.

            </p>

            <div class="flex items-center gap-5">

                <a href="#" class="transition hover:text-[#AE7C18]">

                    Privacy Policy

                </a>

                <a href="#" class="transition hover:text-[#AE7C18]">

                    Terms & Conditions

                </a>

            </div>

        </div>

    </x-ui.container>

</footer>