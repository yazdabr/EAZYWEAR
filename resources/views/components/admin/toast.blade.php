<div
    x-data="toast()"
    x-on:toast.window="show($event.detail)"
    x-init="
        @if(session()->has('success'))
            show({ type: 'success', title: 'Berhasil!', message: '{{ session('success') }}' });
        @elseif(session()->has('error'))
            show({ type: 'error', title: 'Gagal!', message: '{{ session('error') }}' });
        @endif
    "
    class="pointer-events-none fixed inset-x-4 top-6 z-[9999] flex flex-col items-center space-y-3 sm:left-auto sm:right-6 sm:items-end">

    <template
        x-for="item in notifications"
        :key="item.id">

        {{-- UBAH x-show="true" MENJADI x-show="item.visible" --}}
        <div
            x-show="item.visible"
            x-cloak
            x-transition:enter="transform transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
            x-transition:enter-start="opacity-0 -translate-y-3 sm:translate-y-0 sm:translate-x-full"
            x-transition:enter-end="opacity-100 translate-y-0 translate-x-0"
            x-transition:leave="transform transition-all duration-300 ease-in"
            x-transition:leave-start="opacity-100 translate-y-0 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-y-3 sm:translate-y-0 sm:translate-x-full"
            class="pointer-events-auto flex w-full max-w-[360px] items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">

            {{-- Icon --}}
            <div
                class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-emerald-100': item.type=='success',
                    'bg-red-100': item.type=='error',
                    'bg-amber-100': item.type=='warning',
                    'bg-sky-100': item.type=='info'
                }">

                {{-- Success --}}
                <svg
                    x-show="item.type=='success'"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-emerald-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>

                {{-- Error --}}
                <x-heroicon-o-x-circle
                    x-show="item.type=='error'"
                    class="h-5 w-5 text-red-600"/>

                {{-- Warning --}}
                <x-heroicon-o-exclamation-triangle
                    x-show="item.type=='warning'"
                    class="h-5 w-5 text-amber-600"/>

                {{-- Info --}}
                <x-heroicon-o-information-circle
                    x-show="item.type=='info'"
                    class="h-5 w-5 text-sky-600"/>
            </div>

            <div class="flex-1">
                <h4
                    class="font-semibold text-slate-900"
                    x-text="item.title">
                </h4>
                <p
                    class="mt-1 text-sm text-slate-500"
                    x-text="item.message">
                </p>
            </div>

            <button
                @click="remove(item.id)"
                class="text-slate-400 hover:text-slate-700">
                <x-heroicon-o-x-mark
                    class="h-5 w-5"/>
            </button>
        </div>

    </template>

</div>

<script>
function toast() {
    return {
        notifications: [],
        show(data) {
            const id = Date.now() + Math.random();
            const notification = {
                id,
                type: data.type ?? 'success',
                title: data.title,
                message: data.message,
                visible: false // Inisialisasi awal false
            };

            this.notifications.push(notification);

            // Berikan jeda 50ms agar DOM terpasang sebelum merubah visible ke true (memicu x-transition)
            setTimeout(() => {
                const item = this.notifications.find(n => n.id === id);
                if (item) item.visible = true;
            }, 50);

            // Auto-hide setelah 4 detik
            setTimeout(() => {
                this.remove(id);
            }, 4000);
        },
        remove(id) {
            const item = this.notifications.find(n => n.id === id);
            if (item) {
                item.visible = false; // Memicu animasi leave terlebih dahulu
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 350); // Sesuai durasi duration-350 di x-transition:leave
            }
        }
    }
}
</script>