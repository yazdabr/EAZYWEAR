@php
$contacts = [
    [
        'title' => 'WhatsApp',
        'value' => '+62 857 5443 1105',
        'badge' => 'Custom Jersey',
        'action_label' => 'Chat Now',
        'href' => 'https://wa.me/6285754431105',
        'icon' => 'chat-bubble-left-right',
        'card_bg' => 'bg-emerald-50/60 border-emerald-200/80 hover:bg-emerald-50 hover:border-emerald-400',
        'brand_color' => 'bg-emerald-500 text-white group-hover:bg-emerald-600',
        'btn_color' => 'bg-emerald-600 text-white shadow-sm group-hover:bg-emerald-700',
    ],
    [
        'title' => 'Instagram',
        'value' => '@eazywear_',
        'badge' => null,
        'action_label' => 'Follow',
        'href' => 'https://www.instagram.com/eazywear_',
        'icon' => 'camera',
        'card_bg' => 'bg-rose-50/60 border-rose-200/80 hover:bg-rose-50 hover:border-rose-400',
        'brand_color' => 'bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white',
        'btn_color' => 'bg-rose-600 text-white shadow-sm group-hover:bg-rose-700',
    ],
    [
        'title' => 'TikTok',
        'value' => '@eazywear',
        'badge' => null,
        'action_label' => 'Visit',
        'href' => 'https://www.tiktok.com/@eazywear',
        'icon' => 'video-camera',
        'card_bg' => 'bg-slate-100/70 border-slate-200 hover:bg-slate-100 hover:border-slate-400',
        'brand_color' => 'bg-slate-900 text-white group-hover:bg-black',
        'btn_color' => 'bg-slate-900 text-white shadow-sm group-hover:bg-black',
    ],
    [
        'title' => 'Headquarters',
        'value' => "Jl. Asang Permai No.Km 11.200\nBanjar, South Kalimantan",
        'badge' => null,
        'action_label' => 'Open Map',
        'href' => 'https://maps.app.goo.gl/RyfBQ86cTGevVaa98',
        'icon' => 'map-pin',
        'card_bg' => 'bg-[#AE7C18]/5 border-[#AE7C18]/20 hover:bg-[#AE7C18]/10 hover:border-[#AE7C18]/50',
        'brand_color' => 'bg-[#AE7C18] text-white group-hover:bg-[#96690F]',
        'btn_color' => 'bg-[#AE7C18] text-white shadow-sm group-hover:bg-[#96690F]',
    ],
];
@endphp

<section class="bg-slate-50/50 py-10 sm:py-16 md:py-24">
    <x-ui.container>
        <div class="mx-auto flex max-w-xl flex-col gap-3.5 sm:gap-5">

            @foreach($contacts as $contact)
                <x-ui.reveal :index="$loop->index">
                    <a
                        href="{{ $contact['href'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group relative flex w-full items-center justify-between rounded-2xl border p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md sm:p-5 {{ $contact['card_bg'] }}"
                    >
                        <div class="flex items-center gap-3.5 sm:gap-4">
                            {{-- Icon Box dengan Warna Brand khas --}}
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $contact['brand_color'] }} shadow-sm transition-transform duration-300 group-hover:scale-105 sm:h-12 sm:w-12">
                                @switch($contact['icon'])
                                    @case('chat-bubble-left-right')
                                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 sm:h-6 sm:w-6"/>
                                        @break
                                    @case('camera')
                                        <x-heroicon-o-camera class="h-5 w-5 sm:h-6 sm:w-6"/>
                                        @break
                                    @case('video-camera')
                                        <x-heroicon-o-video-camera class="h-5 w-5 sm:h-6 sm:w-6"/>
                                        @break
                                    @case('map-pin')
                                        <x-heroicon-o-map-pin class="h-5 w-5 sm:h-6 sm:w-6"/>
                                        @break
                                @endswitch
                            </div>

                            {{-- Text Content --}}
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900 sm:text-base">
                                        {{ $contact['title'] }}
                                    </h3>
                                    
                                    {{-- Badge Teks Custom Jersey --}}
                                    @if($contact['badge'])
                                        <span class="inline-flex items-center rounded-full bg-emerald-600/10 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                            {{ $contact['badge'] }}
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-0.5 whitespace-pre-line text-xs leading-relaxed text-slate-600 sm:text-sm">
                                    {{ $contact['value'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Action Button Solid di kanan --}}
                        <div class="ml-2 flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-300 {{ $contact['btn_color'] }}">
                            <span class="hidden sm:inline">{{ $contact['action_label'] }}</span>
                            <x-heroicon-o-arrow-up-right class="h-4 w-4 transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                        </div>
                    </a>
                </x-ui.reveal>
            @endforeach

            {{-- Business Hours Card --}}
            <x-ui.reveal :index="count($contacts)">
                <div class="w-full rounded-2xl bg-slate-900 p-4 text-white shadow-sm ring-1 ring-slate-800 sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18] text-white sm:h-12 sm:w-12">
                            <x-heroicon-o-clock class="h-5 w-5 sm:h-6 sm:w-6"/>
                        </div>

                        <div>
                            <h2 class="text-[10px] font-bold uppercase tracking-widest text-[#AE7C18] sm:text-xs">
                                Schedule
                            </h2>

                            <p class="text-sm font-semibold text-white sm:text-base">
                                Business Hours
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2.5 border-t border-slate-800 pt-3.5 text-xs sm:mt-6 sm:space-y-3 sm:pt-4 sm:text-sm">
                        <div class="flex items-center justify-between text-slate-300">
                            <span>Monday - Saturday</span>
                            <span class="font-medium text-white">09:00 - 18:00</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-300">
                            <span>Sunday</span>

                            <span class="inline-flex items-center rounded-full bg-rose-500/10 px-2 py-0.5 text-[10px] font-medium text-rose-400 sm:px-2.5 sm:text-xs">
                                Closed
                            </span>
                        </div>
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </x-ui.container>
</section>