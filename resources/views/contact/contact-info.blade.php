@php
$contacts = [
    [
        'title' => 'WhatsApp',
        'value' => '+62 857 5443 1105',
        'href' => 'https://wa.me/6285754431105',
        'icon' => 'chat-bubble-left-right',
    ],
    // [
    //     'title' => 'Email Us',
    //     'value' => 'contact@eazywear.id',
    //     'href' => 'mailto:contact@eazywear.id',
    //     'icon' => 'envelope',
    // ],
    [
        'title' => 'Headquarters',
        'value' => "Jl. Asang Permai No.Km 11.200\nBanjar, South Kalimantan",
        'href' => 'https://maps.app.goo.gl/RyfBQ86cTGevVaa98',
        'icon' => 'map-pin',
    ],
];
@endphp

<section class="bg-slate-50/50 py-16 md:py-24">
    <x-ui.container>
        {{-- Menggunakan max-w-xl & mx-auto agar seluruh kartu berurutan ke bawah dan rata tengah dengan ukuran sama --}}
        <div class="mx-auto flex max-w-xl flex-col gap-6">

            @foreach($contacts as $contact)
                <x-ui.reveal :index="$loop->index">
                    <a
                        href="{{ $contact['href'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group relative flex w-full items-start justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-[#AE7C18]/40 hover:shadow-md hover:shadow-[#AE7C18]/10"
                    >
                        <div class="flex items-start gap-4">
                            {{-- Icon Box --}}
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18] transition-colors duration-300 group-hover:bg-[#AE7C18] group-hover:text-white">
                                @switch($contact['icon'])
                                    @case('chat-bubble-left-right')
                                        <x-heroicon-o-chat-bubble-left-right class="h-6 w-6"/>
                                        @break
                                    @case('envelope')
                                        <x-heroicon-o-envelope class="h-6 w-6"/>
                                        @break
                                    @case('map-pin')
                                        <x-heroicon-o-map-pin class="h-6 w-6"/>
                                        @break
                                @endswitch
                            </div>

                            {{-- Text Content --}}
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 transition-colors group-hover:text-[#AE7C18]">
                                    {{ $contact['title'] }}
                                </h3>
                                <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-600">
                                    {{ $contact['value'] }}
                                </p>
                            </div>
                        </div>

                        {{-- External Link Arrow Indicator --}}
                        <x-heroicon-o-arrow-up-right class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-[#AE7C18]" />
                    </a>
                </x-ui.reveal>
            @endforeach

            {{-- Business Hours Card --}}
            <x-ui.reveal :index="count($contacts)">
                <div class="w-full rounded-2xl bg-slate-900 p-6 text-white shadow-sm ring-1 ring-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18] text-white">
                            <x-heroicon-o-clock class="h-6 w-6"/>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-widest text-[#AE7C18]">
                                Schedule
                            </h3>
                            <p class="text-base font-semibold text-white">
                                Business Hours
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3 border-t border-slate-800 pt-4 text-sm">
                        <div class="flex items-center justify-between text-slate-300">
                            <span>Monday - Saturday</span>
                            <span class="font-medium text-white">09:00 - 18:00</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-300">
                            <span>Sunday</span>
                            <span class="inline-flex items-center rounded-full bg-rose-500/10 px-2.5 py-0.5 text-xs font-medium text-rose-400">
                                Closed
                            </span>
                        </div>
                    </div>
                </div>
            </x-ui.reveal>

        </div>
    </x-ui.container>
</section>