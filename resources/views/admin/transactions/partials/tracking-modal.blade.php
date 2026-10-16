<div id="shipment-tracking-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="shipment-tracking-title">

    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h2 id="shipment-tracking-title" class="text-base font-bold text-slate-900">
                    Tracking Resi
                </h2>
                <p class="mt-1 text-xs text-slate-500">Riwayat perjalanan paket</p>
            </div>

            <button type="button"
                    onclick="closeShipmentTrackingModal()"
                    class="rounded-lg px-3 py-2 text-lg text-slate-500 hover:bg-slate-100"
                    aria-label="Tutup modal">
                &times;
            </button>
        </div>

        <div class="max-h-[70vh] space-y-4 overflow-y-auto p-5">
            <p id="shipment-tracking-loading" class="text-sm text-slate-500">
                Memuat informasi tracking...
            </p>

            <div id="shipment-tracking-error"
                 class="hidden rounded-xl bg-red-50 p-3 text-sm text-red-700"
                 role="alert"></div>

            <div id="shipment-tracking-content" class="hidden">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Nomor resi</p>
                    <p id="shipment-tracking-number"
                       class="mt-1 break-all text-sm font-bold text-slate-900">-</p>

                    <p class="mt-3 text-xs text-slate-500">Kurir</p>
                    <p id="shipment-tracking-courier"
                       class="mt-1 text-sm font-semibold text-slate-900">-</p>

                    <p class="mt-3 text-xs text-slate-500">Status terkini</p>
                    <p id="shipment-tracking-status"
                       class="mt-1 text-sm font-bold text-amber-700">-</p>
                </div>

                <h3 class="mb-3 mt-5 text-sm font-bold text-slate-900">
                    Riwayat pengiriman
                </h3>

                <div id="shipment-tracking-history" class="space-y-4"></div>
            </div>
        </div>

        <div class="border-t border-slate-100 p-4 text-right">
            <button type="button"
                    onclick="closeShipmentTrackingModal()"
                    class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('shipment-tracking-modal');
    if (!modal || modal.dataset.initialized === 'true') return;
    modal.dataset.initialized = 'true';

    const getElement = (id) => document.getElementById(id);

    function setText(id, value) {
        getElement(id).textContent =
            value === null || value === undefined || value === ''
                ? '-'
                : String(value);
    }

    function addHistoryItem(item) {
        const entry = document.createElement('div');
        entry.className = 'relative border-l-2 border-slate-200 pb-1 pl-4';

        const title = document.createElement('p');
        title.className = 'text-sm font-semibold text-slate-900';
        title.textContent =
            item.status || item.event || item.description ||
            item.note || item.message || 'Pembaruan tracking';

        entry.appendChild(title);

        const detailText =
            item.note || item.message ||
            (item.description !== title.textContent ? item.description : null) ||
            item.location;

        if (detailText) {
            const detail = document.createElement('p');
            detail.className = 'mt-1 text-xs leading-relaxed text-slate-600';
            detail.textContent = String(detailText);
            entry.appendChild(detail);
        }

        const dateText =
            item.updated_at || item.created_at || item.datetime ||
            item.date || item.timestamp;

        if (dateText) {
            const date = document.createElement('p');
            date.className = 'mt-1 text-xs text-slate-400';
            date.textContent = String(dateText);
            entry.appendChild(date);
        }

        getElement('shipment-tracking-history').appendChild(entry);
    }

    window.closeShipmentTrackingModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    window.openShipmentTrackingModal = async function (url) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        getElement('shipment-tracking-loading').classList.remove('hidden');
        getElement('shipment-tracking-error').classList.add('hidden');
        getElement('shipment-tracking-content').classList.add('hidden');
        getElement('shipment-tracking-history').replaceChildren();

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Data tracking belum tersedia.');
            }

            const data = result.data || {};

            setText('shipment-tracking-number', data.tracking_number);
            setText('shipment-tracking-courier', data.courier);
            setText('shipment-tracking-status', data.status || 'Status belum tersedia');

            const history = Array.isArray(data.history) ? data.history : [];

            if (history.length) {
                history.forEach(addHistoryItem);
            } else {
                const empty = document.createElement('p');
                empty.className = 'text-sm text-slate-500';
                empty.textContent = 'Belum ada riwayat perjalanan yang tersedia.';
                getElement('shipment-tracking-history').appendChild(empty);
            }

            getElement('shipment-tracking-content').classList.remove('hidden');
        } catch (error) {
            getElement('shipment-tracking-error').textContent =
                error.message || 'Tracking gagal dimuat. Silakan coba kembali.';
            getElement('shipment-tracking-error').classList.remove('hidden');
        } finally {
            getElement('shipment-tracking-loading').classList.add('hidden');
        }
    };

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            window.closeShipmentTrackingModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            window.closeShipmentTrackingModal();
        }
    });
})();
</script>
