<div
    id="customer-tracking-modal"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="customer-tracking-title"
>
    <div class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 id="customer-tracking-title" class="text-base font-bold text-slate-900">Tracking Resi</h2>
                <p class="mt-1 text-xs text-slate-500">Riwayat perjalanan paket</p>
            </div>
            <button type="button" onclick="closeCustomerTrackingModal()"
                class="rounded-lg px-3 py-2 text-lg text-slate-500 hover:bg-slate-100"
                aria-label="Tutup modal">&times;</button>
        </div>

        <div class="space-y-4 overflow-y-auto p-5">
            <div id="customer-tracking-shipment-list" class="space-y-2">
                @foreach($transaction->shipments->filter(fn ($shipment) => filled($shipment->biteship_tracking_id)) as $shipment)
                    <button
                        type="button"
                        data-tracking-url="{{ route('orders.tracking.shipment', [
                            'invoice' => $transaction->invoice_number,
                            'shipment' => $shipment->id,
                            'email' => $transaction->shipping_email,
                        ]) }}"
                        onclick="loadCustomerTracking(this.dataset.trackingUrl)"
                        class="w-full rounded-xl border border-slate-200 p-3 text-left transition hover:border-[#AE7C18] hover:bg-amber-50/40"
                    >
                        <span class="block text-xs text-slate-500">
                            {{ $shipment->shipment_type === 'combined' ? 'Pengiriman Gabungan' : 'Pengiriman' }}
                        </span>
                        <span class="mt-1 block break-all text-sm font-bold text-slate-900">
                            {{ $shipment->tracking_number ?: $shipment->biteship_waybill_id ?: 'Nomor resi belum tersedia' }}
                        </span>
                        <span class="mt-1 block text-xs text-slate-500">
                            {{ $shipment->courier ?: $shipment->shipping_method ?: '-' }}
                        </span>
                    </button>
                @endforeach
            </div>

            <p id="customer-tracking-loading" class="hidden text-sm text-slate-500">
                Memuat riwayat pengiriman...
            </p>
            <div id="customer-tracking-error" class="hidden rounded-xl bg-red-50 p-3 text-sm text-red-700" role="alert"></div>

            <div id="customer-tracking-content" class="hidden">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Nomor resi</p>
                    <p id="customer-tracking-number" class="mt-1 break-all text-sm font-bold text-slate-900">-</p>
                    <p class="mt-3 text-xs text-slate-500">Kurir</p>
                    <p id="customer-tracking-courier" class="mt-1 text-sm font-semibold text-slate-900">-</p>
                </div>

                <h3 class="mb-3 mt-5 text-sm font-bold text-slate-900">Riwayat pengiriman</h3>
                <div id="customer-tracking-history" class="space-y-4"></div>
            </div>
        </div>

        <div class="border-t border-slate-100 p-4 text-right">
            <button type="button" onclick="closeCustomerTrackingModal()"
                class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Tutup</button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('customer-tracking-modal');
    if (!modal || modal.dataset.initialized === 'true') return;
    modal.dataset.initialized = 'true';

    const el = (id) => document.getElementById(id);

    function setText(id, value) {
        el(id).textContent = value == null || value === '' ? '-' : String(value);
    }

    function formatDate(value) {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return String(value);

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone: 'Asia/Makassar'
        }).format(date) + ' WITA';
    }

    function renderHistory(items) {
        const container = el('customer-tracking-history');
        container.replaceChildren();

        const visibleItems = items.filter(item =>
            item.note || item.message || item.description || item.location ||
            item.updated_at || item.created_at || item.datetime || item.date || item.timestamp
        );

        if (!visibleItems.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-slate-500';
            empty.textContent = 'Belum ada riwayat perjalanan yang tersedia.';
            container.appendChild(empty);
            return;
        }

        visibleItems.forEach(item => {
            const entry = document.createElement('div');
            entry.className = 'border-l-2 border-slate-200 pb-2 pl-4';

            const description = item.note || item.message || item.description || item.location;
            if (description) {
                const detail = document.createElement('p');
                detail.className = 'text-xs leading-relaxed text-slate-600';
                detail.textContent = String(description);
                entry.appendChild(detail);
            }

            const dateValue = item.updated_at || item.created_at || item.datetime ||
                item.date || item.timestamp;

            if (dateValue) {
                const date = document.createElement('p');
                date.className = 'mt-1 text-xs text-slate-400';
                date.textContent = formatDate(dateValue);
                entry.appendChild(date);
            }

            container.appendChild(entry);
        });
    }

    window.openCustomerTrackingModal = function () {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const first = modal.querySelector('[data-tracking-url]');
        if (first) window.loadCustomerTracking(first.dataset.trackingUrl);
    };

    window.closeCustomerTrackingModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.loadCustomerTracking = async function (url) {
        el('customer-tracking-loading').classList.remove('hidden');
        el('customer-tracking-error').classList.add('hidden');
        el('customer-tracking-content').classList.add('hidden');
        el('customer-tracking-history').replaceChildren();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Tracking belum tersedia.');
            }

            const data = result.data || {};
            setText('customer-tracking-number', data.tracking_number);
            setText('customer-tracking-courier', data.courier);
            renderHistory(Array.isArray(data.history) ? data.history : []);
            el('customer-tracking-content').classList.remove('hidden');
        } catch (error) {
            el('customer-tracking-error').textContent =
                error.message || 'Tracking gagal dimuat. Silakan coba kembali.';
            el('customer-tracking-error').classList.remove('hidden');
        } finally {
            el('customer-tracking-loading').classList.add('hidden');
        }
    };

    modal.addEventListener('click', event => {
        if (event.target === modal) window.closeCustomerTrackingModal();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            window.closeCustomerTrackingModal();
        }
    });
})();
</script>
