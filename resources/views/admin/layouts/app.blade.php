<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="{ sidebarOpen: false }"
    class="h-full scroll-smooth"
>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F5F6FA] font-sans text-slate-800 antialiased">

    {{-- ================= MOBILE OVERLAY ================= --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen=false"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
    ></div>

    {{-- ================= SIDEBAR ================= --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 w-[260px] -translate-x-full transform transition-transform duration-300 ease-in-out lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        @include('admin.layouts.sidebar')
    </aside>

    {{-- ================= CONTENT ================= --}}
    <div class="min-h-screen transition-all duration-300 lg:ml-[260px]">

        {{-- Navbar --}}
        @include('admin.layouts.navbar')

        {{-- Main Content --}}
        <main class="p-6 pt-[109px] sm:p-6 sm:pt-[109px] lg:p-8 lg:pt-8">
            @yield('content')
        </main>

    </div>

    {{-- ================= TOAST ================= --}}
    <div
        id="toast-container"
        class="fixed right-6 top-6 z-[9999] space-y-3"
    ></div>

    @stack('scripts')

    @if(auth()->check() && in_array(auth()->user()->role, ['super_admin', 'management'], true))
        @php
            $paymentSoundEnabled = (bool) auth()->user()->payment_sound_enabled;
        @endphp
        <div id="payment-sound-control" class="fixed bottom-5 right-5 z-[9998]">
            <button type="button" id="enable-payment-sound"
                style="display:none"
                class="rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg hover:bg-slate-700">
                Aktifkan Audio di Perangkat Ini
            </button>
            <span id="payment-sound-status"
                class="ml-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-700 shadow {{ $paymentSoundEnabled ? '' : 'hidden' }}">
                Preferensi suara aktif
            </span>
        </div>

        <audio id="payment-money-audio" src="{{ asset('sounds/uangmasuk.mp3') }}" preload="auto"></audio>

        <script>
        (() => {
            const endpoint = @json(route('admin.transactions.payment-alerts'));
            const preferenceEndpoint = @json(route('admin.transactions.payment-sound-preference'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const audio = document.getElementById('payment-money-audio');
            const button = document.getElementById('enable-payment-sound');
            const status = document.getElementById('payment-sound-status');
            if (!audio || !button || !status) return;

            let enabled = @json($paymentSoundEnabled);
            let unlocked = localStorage.getItem('paymentSoundUnlocked') === 'true';
            let polling = false;
            let lastSeen = Math.floor(Date.now() / 1000);
            const seen = new Set();
            const queue = [];
            let playing = false;

            async function savePreference(value) {
                const response = await fetch(preferenceEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ enabled: value })
                });
                if (!response.ok) throw new Error('Preferensi gagal disimpan.');
                return response.json();
            }

            async function loadPreference() {
                const response = await fetch(preferenceEndpoint, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) throw new Error('Preferensi gagal dibaca.');
                const data = await response.json();
                enabled = Boolean(data.enabled);
                updateUI();
                if (enabled) checkPayments(true);
            }

            function updateUI() {
                button.textContent = enabled
                    ? 'Aktifkan Audio di Perangkat Ini'
                    : 'Aktifkan Suara Notifikasi';
                button.classList.toggle('hidden', enabled && unlocked);
                button.style.display = (enabled && unlocked) ? 'none' : '';
                status.classList.toggle('hidden', !enabled);
                status.textContent = enabled ? 'Suara aktif' : 'Suara nonaktif';
            }

            async function playQueue() {
                if (playing || !enabled || !unlocked) return;
                playing = true;
                try {
                    while (queue.length && enabled && unlocked) {
                        queue.shift();
                        try {
                            audio.currentTime = 0;
                            await audio.play();
                            await new Promise(resolve => {
                                audio.addEventListener('ended', resolve, { once: true });
                                audio.addEventListener('error', resolve, { once: true });
                            });
                        } catch (error) {
                            unlocked = false;
                            localStorage.removeItem('paymentSoundUnlocked');
                            updateUI();
                            console.warn('Audio membutuhkan aktivasi ulang:', error);
                            break;
                        }
                    }
                } finally {
                    playing = false;
                }
            }

            async function checkPayments(initial = false) {
                if (polling || !enabled) return;
                polling = true;
                try {
                    const response = await fetch(
                        endpoint + '?since=' + encodeURIComponent(Math.max(0, lastSeen - 5)),
                        {
                            credentials: 'same-origin',
                            cache: 'no-store',
                            headers: { 'Accept': 'application/json' }
                        }
                    );
                    if (!response.ok) throw new Error('Gagal mengambil pembayaran.');
                    const data = await response.json();

                    for (const item of (data.transactions || [])) {
                        const id = String(item.id);
                        if (seen.has(id)) continue;
                        seen.add(id);
                        if (!initial) queue.push(id);
                    }

                    lastSeen = Math.max(lastSeen, Number(data.server_time) || lastSeen);
                    if (!initial && queue.length) playQueue();
                } catch (error) {
                    console.error('Pengecekan pembayaran gagal:', error);
                } finally {
                    polling = false;
                }
            }

            button.addEventListener('click', async () => {
                try {
                    audio.volume = 1;
                    audio.currentTime = 0;
                    await audio.play();
                    unlocked = true;
                    localStorage.setItem('paymentSoundUnlocked', 'true');

                    if (!enabled) {
                        await savePreference(true);
                        enabled = true;
                    }

                    updateUI();
                    await checkPayments(true);
                } catch (error) {
                    unlocked = false;
                    updateUI();
                    alert('Audio gagal diputar atau preferensi gagal disimpan. Periksa MP3 dan koneksi.');
                    console.error(error);
                }
            });

            loadPreference().catch(error => console.error(error));
            setInterval(() => checkPayments(false), 5000);
        })();
        </script>
    @endif

    <x-admin.toast />

</body>
</html>
