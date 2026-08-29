<section class="bg-white py-12 sm:py-20 lg:py-28">
    <x-ui.container>
        {{-- Heading --}}
        <x-ui.reveal>
            <div class="mb-10 text-center sm:mb-16 lg:mb-20">
                <p class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-3 sm:text-xs lg:tracking-[0.3em]">
                    HOW IT WORKS
                </p>

                <h2 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    Seamless Production
                </h2>
            </div>
        </x-ui.reveal>

        {{-- Timeline --}}
        <div class="relative">
            {{-- Line Desktop Horizontal --}}
            <div class="absolute left-0 right-0 top-10 hidden h-[2px] bg-gray-300 lg:block"></div>

            {{-- Line Mobile Vertical --}}
            <div class="absolute bottom-6 left-6 top-6 w-[2px] bg-gray-200 sm:left-8 lg:hidden"></div>

            <div class="grid gap-6 sm:gap-8 md:grid-cols-2 lg:grid-cols-5">

                {{-- Step 1 --}}
                <x-ui.reveal animation="right" :index="0">
                    <x-website.process-card
                        :step="1"
                        title="Consultation"
                        description="Discuss your team's identity, fabric preferences, and order quantity."
                    />
                </x-ui.reveal>

                {{-- Step 2 --}}
                <x-ui.reveal animation="right" :index="1">
                    <x-website.process-card
                        :step="2"
                        title="Design"
                        description="Our designers create custom mockups until you are completely satisfied."
                    />
                </x-ui.reveal>

                {{-- Step 3 --}}
                <x-ui.reveal animation="right" :index="2">
                    <x-website.process-card
                        :step="3"
                        title="Production"
                        description="High-quality sublimation printing and precision sewing process begin."
                    />
                </x-ui.reveal>

                {{-- Step 4 --}}
                <x-ui.reveal animation="right" :index="3">
                    <x-website.process-card
                        :step="4"
                        title="Quality Check"
                        description="Every jersey is carefully inspected before packaging and shipment."
                    />
                </x-ui.reveal>

                {{-- Step 5 --}}
                <x-ui.reveal animation="right" :index="4">
                    <x-website.process-card
                        :step="5"
                        title="Delivery"
                        description="Products are securely packed and shipped to your location across Indonesia."
                    />
                </x-ui.reveal>

            </div>
        </div>
    </x-ui.container>
</section>