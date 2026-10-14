document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Shipping Map
    |--------------------------------------------------------------------------
    */

    const mapElement = document.getElementById('shipping-map');

    if (mapElement && typeof L !== 'undefined') {
        const latitudeInput = document.getElementById('shipping_latitude');
        const longitudeInput = document.getElementById('shipping_longitude');
        const locationButton = document.getElementById('use-my-location');

        if (latitudeInput && longitudeInput) {
            const defaultLatitude = -3.3194;
            const defaultLongitude = 114.5908;

            const oldLatitude = parseFloat(latitudeInput.value);
            const oldLongitude = parseFloat(longitudeInput.value);

            const hasOldLocation =
                Number.isFinite(oldLatitude) &&
                Number.isFinite(oldLongitude);

            const initialLatitude = hasOldLocation
                ? oldLatitude
                : defaultLatitude;

            const initialLongitude = hasOldLocation
                ? oldLongitude
                : defaultLongitude;

            const map = L.map(mapElement).setView(
                [initialLatitude, initialLongitude],
                hasOldLocation ? 16 : 12
            );

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }
            ).addTo(map);

            const marker = L.marker(
                [initialLatitude, initialLongitude],
                {
                    draggable: true
                }
            ).addTo(map);

            function setLocation(latitude, longitude) {
                latitudeInput.value = Number(latitude).toFixed(7);
                longitudeInput.value = Number(longitude).toFixed(7);

                marker.setLatLng([
                    latitude,
                    longitude
                ]);

                map.setView(
                    [latitude, longitude],
                    16
                );
            }

            marker.on('dragend', function () {
                const position = marker.getLatLng();

                setLocation(
                    position.lat,
                    position.lng
                );
            });

            if (hasOldLocation) {
                setLocation(
                    oldLatitude,
                    oldLongitude
                );
            }

            if (locationButton) {
                locationButton.addEventListener(
                    'click',
                    function () {
                        if (!navigator.geolocation) {
                            alert(
                                'Browser Anda tidak mendukung lokasi perangkat.'
                            );

                            return;
                        }

                        locationButton.disabled = true;
                        locationButton.textContent =
                            'Mencari lokasi...';

                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                setLocation(
                                    position.coords.latitude,
                                    position.coords.longitude
                                );

                                locationButton.disabled = false;

                                locationButton.textContent =
                                    'Gunakan Lokasi Saya';
                            },
                            function () {
                                alert(
                                    'Lokasi tidak dapat diakses. Pastikan izin lokasi browser telah diberikan.'
                                );

                                locationButton.disabled = false;

                                locationButton.textContent =
                                    'Gunakan Lokasi Saya';
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    }
                );
            }

            setTimeout(function () {
                map.invalidateSize();
            }, 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('checkout-form');

    if (!form) {
        return;
    }

    const postalInput = document.getElementById('shipping_postal_code');
    const latitudeInput = document.getElementById('shipping_latitude');
    const longitudeInput = document.getElementById('shipping_longitude');
    const vaBankSection = document.getElementById('va-bank-section');
    const paymentMethodInputs = form.querySelectorAll('input[name="payment_method"]');
    const vaBankInputs = form.querySelectorAll('input[name="va_bank"]');
    const fulfillmentModeInputs = form.querySelectorAll('input[name="fulfillment_mode"]');

    if (!postalInput || !vaBankSection || paymentMethodInputs.length === 0 || vaBankInputs.length === 0) {
        return;
    }

    const config = {
        shippingRatesUrl: form.dataset.shippingRatesUrl,
        fulfillmentAvailabilityUrl: form.dataset.fulfillmentAvailabilityUrl,
        subtotal: Number(form.dataset.subtotal || 0),
        specialBatchStart: form.dataset.specialBatchStart,
        specialBatchEnd: form.dataset.specialBatchEnd,
        specialBatchCapacity: Number(form.dataset.specialBatchCapacity || 100),
        pickupStartTime: form.dataset.pickupStartTime,
        pickupEndTime: form.dataset.pickupEndTime,
        pickupSameDayCutoff: form.dataset.pickupSameDayCutoff,
        checkoutDate: form.dataset.checkoutDate,
        checkoutTime: form.dataset.checkoutTime,
        jntLogo: form.dataset.jntLogo,
        lionLogo: form.dataset.lionLogo
    };

    const groupElements = Array.from(form.querySelectorAll('[data-fulfillment-group]'));
    const groups = groupElements.map((element) => ({
        element,
        type: element.dataset.groupType,
        label: element.dataset.groupLabel,
        subtotal: Number(element.dataset.groupSubtotal || 0),
        combinedItemCount: Number(element.dataset.combinedItemCount || 0),
        combinedSubtotal: Number(element.dataset.combinedSubtotal || 0),
        titleElement: element.querySelector('[data-group-title]'),
        summaryElement: element.querySelector('[data-group-summary]'),
        modeBadge: element.querySelector('[data-group-mode-badge]'),
        methodInputs: element.querySelectorAll('input[type="radio"][name$="_shipping_method"]'),
        pickupSection: element.querySelector('[data-pickup-section]'),
        pickupDate: element.querySelector('[data-pickup-date]'),
        pickupTimeStart: element.querySelector('[data-pickup-time-start]'),
        pickupTimeEnd: element.querySelector('[data-pickup-time-end]'),
        pickupStatus: element.querySelector('[data-pickup-availability-status]'),
        pickupError: element.querySelector('[data-pickup-validation-error]'),
        ratesSection: element.querySelector('[data-shipping-rates-section]'),
        ratesList: element.querySelector('[data-shipping-rates-list]'),
        ratesStatus: element.querySelector('[data-shipping-rates-status]'),
        ratesError: element.querySelector('[data-shipping-rates-error]'),
        ratesHint: element.querySelector('[data-shipping-rates-hint]'),
        courierCode: element.querySelector('[data-courier-code]'),
        courierServiceCode: element.querySelector('[data-courier-service-code]'),
        costElement: document.getElementById(`shipping-cost-${element.dataset.groupType}`),
        selectedRate: null,
        requestSequence: 0,
        availabilityRequest: 0,
        cost: null
    }));

    const getFulfillmentMode = () =>
        form.querySelector('input[name="fulfillment_mode"]:checked')?.value || 'separate';

    const getGroupByType = (type) =>
        groups.find((group) => group.type === type);

    function syncCombinedMethods(preferredMethod = null) {
        if (getFulfillmentMode() !== 'combined') return;

        const ready = getGroupByType('ready');
        const preOrder = getGroupByType('pre_order');
        if (!ready || !preOrder) return;

        const method = preferredMethod ||
            getShippingMethod(preOrder) ||
            getShippingMethod(ready);

        if (!method) return;

        [ready, preOrder].forEach((group) => {
            const matchingInput = Array.from(group.methodInputs)
                .find((input) => input.value === method);

            if (matchingInput) matchingInput.checked = true;
        });
    }

    let debounceTimer = null;

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function addDays(dateString, days) {
        const date = new Date(`${dateString}T00:00:00`);
        date.setDate(date.getDate() + days);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDisplayDate(dateString) {
        if (!dateString) return '';
        const [year, month, day] = dateString.split('-');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return `${day} ${months[Number(month) - 1]} ${year}`;
    }

    function updateTotals() {
        let totalShipping = 0;
        let incompleteCourier = false;

        groups.forEach((group) => {
            if (group.cost === null) {
                if (getShippingMethod(group) === 'Kurir') incompleteCourier = true;
            } else {
                totalShipping += Number(group.cost);
            }
        });

        const totalAmount = document.getElementById('total-amount');
        const totalNote = document.getElementById('total-note');
        if (totalAmount) totalAmount.textContent = formatRupiah(config.subtotal + totalShipping);

        if (totalNote) {
            if (incompleteCourier) {
                totalNote.textContent = 'Pilih kurir untuk melihat total';
            } else if (groups.every((group) => group.cost !== null)) {
                totalNote.textContent = 'Termasuk seluruh ongkir';
            } else {
                totalNote.textContent = 'Pilih metode pengiriman';
            }
        }
    }

    function getShippingMethod(group) {
        return group.element.querySelector('input[type="radio"][name$="_shipping_method"]:checked')?.value || '';
    }

    function isSpecialBatchCheckout(group) {
        return group.type === 'pre_order' && config.checkoutDate <= config.specialBatchEnd;
    }

    function getMinimumPickupDate(group) {
        if (isSpecialBatchCheckout(group)) {
            return config.checkoutDate > config.specialBatchStart
                ? config.checkoutDate
                : config.specialBatchStart;
        }

        // Ready Stock selalu memakai aturan pickup normal, bukan jadwal Event Hold.
        return config.checkoutTime < config.pickupSameDayCutoff
            ? config.checkoutDate
            : addDays(config.checkoutDate, 1);
    }

    function clearRatesError(group) {
        group.ratesError.textContent = '';
        group.ratesError.classList.add('hidden');
    }

    function showRatesError(group, message) {
        group.ratesError.textContent = message;
        group.ratesError.classList.remove('hidden');
        group.ratesStatus.textContent = '';
    }

    function clearPickupError(group) {
        group.pickupError.textContent = '';
        group.pickupError.classList.add('hidden');
    }

    function showPickupError(group, message) {
        group.pickupError.textContent = message;
        group.pickupError.classList.remove('hidden');
        group.pickupSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function updatePickupTimeRules(group) {
        group.pickupTimeStart.min = config.pickupStartTime;
        group.pickupTimeStart.max = config.pickupEndTime;
        group.pickupTimeEnd.min = config.pickupStartTime;
        group.pickupTimeEnd.max = config.pickupEndTime;

        const selectedDate = group.pickupDate.value;
        if (!isSpecialBatchCheckout(group) && selectedDate === config.checkoutDate) {
            const minimumTime = config.checkoutTime > config.pickupStartTime
                ? config.checkoutTime
                : config.pickupStartTime;
            group.pickupTimeStart.min = minimumTime;
            group.pickupTimeEnd.min = minimumTime;
        }

        if (group.pickupTimeStart.value) {
            group.pickupTimeEnd.min = group.pickupTimeStart.value;
        }
    }

    function applyPickupDateRules(group) {
        const minimumDate = getMinimumPickupDate(group);
        group.pickupDate.min = minimumDate;

        if (isSpecialBatchCheckout(group)) {
            group.pickupDate.max = config.specialBatchEnd;
        } else {
            group.pickupDate.removeAttribute('max');
        }

        if (group.pickupDate.value && group.pickupDate.value < minimumDate) {
            group.pickupDate.value = minimumDate;
        }

        if (isSpecialBatchCheckout(group) && group.pickupDate.value > config.specialBatchEnd) {
            group.pickupDate.value = minimumDate;
        }

        updatePickupTimeRules(group);
    }

    function validatePickupSchedule(group) {
        const pickupDate = group.pickupDate.value;
        const pickupStart = group.pickupTimeStart.value;
        const pickupEnd = group.pickupTimeEnd.value;

        if (!pickupDate || !pickupStart || !pickupEnd) {
            return 'Silakan lengkapi tanggal dan waktu pengambilan terlebih dahulu.';
        }

        const minimumDate = getMinimumPickupDate(group);
        if (pickupDate < minimumDate) {
            return `Tanggal pickup paling cepat ${formatDisplayDate(minimumDate)}.`;
        }

        if (isSpecialBatchCheckout(group) && pickupDate > config.specialBatchEnd) {
            return `Untuk Pre-order pada periode ini, pickup hanya tersedia sampai ${formatDisplayDate(config.specialBatchEnd)}.`;
        }

        if (
            pickupStart < config.pickupStartTime ||
            pickupStart > config.pickupEndTime ||
            pickupEnd < config.pickupStartTime ||
            pickupEnd > config.pickupEndTime
        ) {
            return `Jam pengambilan hanya tersedia antara ${config.pickupStartTime} sampai ${config.pickupEndTime}.`;
        }

        if (pickupStart > pickupEnd) {
            return 'Waktu mulai tidak boleh lebih dari waktu selesai.';
        }

        if (!isSpecialBatchCheckout(group) && pickupDate === config.checkoutDate) {
            if (config.checkoutTime >= config.pickupSameDayCutoff) {
                return `Pickup hari yang sama hanya tersedia sebelum pukul ${config.pickupSameDayCutoff}. Silakan pilih tanggal berikutnya.`;
            }
            if (pickupStart < config.checkoutTime) {
                return 'Waktu mulai pickup sudah lewat. Silakan pilih waktu yang masih tersedia hari ini atau tanggal berikutnya.';
            }
        }

        return null;
    }

    async function checkPickupDateAvailability(group) {
        if (!group.pickupDate.value || !isSpecialBatchCheckout(group) || !group.pickupStatus) {
            if (group.pickupStatus) {
                group.pickupStatus.textContent = '';
                group.pickupStatus.classList.add('hidden');
            }
            return true;
        }

        const currentRequest = ++group.availabilityRequest;
        group.pickupStatus.textContent = 'Memeriksa ketersediaan tanggal...';
        group.pickupStatus.className = 'mt-1.5 text-[10px] leading-4 text-gray-400 sm:text-xs';

        try {
            const url = new URL(config.fulfillmentAvailabilityUrl, window.location.origin);
            url.searchParams.set('date', group.pickupDate.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            const data = await response.json();

            if (currentRequest !== group.availabilityRequest) return true;
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal memeriksa ketersediaan tanggal.');
            }

            if (!data.available) {
                group.pickupStatus.textContent = data.message || 'Tanggal pickup tersebut sudah penuh. Silakan pilih tanggal lain.';
                group.pickupStatus.className = 'mt-1.5 text-[10px] leading-4 text-red-600 sm:text-xs';
                return false;
            }

            group.pickupStatus.textContent = 'Tanggal pickup masih tersedia.';
            group.pickupStatus.className = 'mt-1.5 text-[10px] leading-4 text-green-600 sm:text-xs';
            return true;
        } catch (error) {
            if (currentRequest !== group.availabilityRequest) return true;
            group.pickupStatus.textContent = error.message || 'Ketersediaan tanggal belum dapat diperiksa.';
            group.pickupStatus.className = 'mt-1.5 text-[10px] leading-4 text-red-600 sm:text-xs';
            return false;
        }
    }

    function resetGroupRateSelection(group, keepHiddenValues = false) {
        const combined = getFulfillmentMode() === 'combined';
        const affectedGroups = combined
            ? groups
            : [group];

        affectedGroups.forEach((item) => {
            item.selectedRate = null;
            item.cost = null;

            if (!keepHiddenValues) {
                item.courierCode.value = '';
                item.courierServiceCode.value = '';
            }

            item.costElement.textContent = 'Akan dihitung';
            item.costElement.className = 'text-[10px] text-gray-400 sm:text-xs';
        });

        updateTotals();
    }

    function selectRate(group, rate, labelElement) {
        clearRatesError(group);

        const combined = getFulfillmentMode() === 'combined';
        const ready = getGroupByType('ready');
        const preOrder = getGroupByType('pre_order');
        const price = Number(rate.price || 0);

        if (combined && ready && preOrder) {
            [ready, preOrder].forEach((item) => {
                item.selectedRate = rate;
                item.courierCode.value = rate.courier_code || '';
                item.courierServiceCode.value = rate.service_code || '';
                clearRatesError(item);
            });

            ready.cost = 0;
            preOrder.cost = price;

            ready.costElement.textContent = 'Termasuk ongkir gabungan';
            ready.costElement.className = 'text-[10px] text-gray-400 sm:text-xs';

            preOrder.costElement.textContent = formatRupiah(price);
            preOrder.costElement.className = 'text-[10px] font-semibold text-[#AE7C18] sm:text-xs';
        } else {
            group.selectedRate = rate;
            group.cost = price;
            group.courierCode.value = rate.courier_code || '';
            group.courierServiceCode.value = rate.service_code || '';
            group.costElement.textContent = formatRupiah(price);
            group.costElement.className = 'text-[10px] font-semibold text-[#AE7C18] sm:text-xs';
        }

        group.ratesList.querySelectorAll('[data-shipping-rate]').forEach((element) => {
            element.classList.remove('border-[#AE7C18]', 'bg-[#AE7C18]/5');
            element.classList.add('border-gray-200');
        });
        labelElement.classList.remove('border-gray-200');
        labelElement.classList.add('border-[#AE7C18]', 'bg-[#AE7C18]/5');

        updateTotals();
    }

    function renderRates(group, rates) {
        group.ratesList.innerHTML = '';
        resetGroupRateSelection(group, true);

        if (!Array.isArray(rates) || rates.length === 0) {
            group.ratesSection.classList.remove('hidden');
            group.ratesStatus.textContent = '';
            showRatesError(group, 'Belum ada layanan pengiriman yang tersedia untuk kelompok pesanan ini.');
            group.ratesHint.textContent = 'Periksa kembali kode pos atau titik lokasi penerima.';
            return;
        }

        group.ratesSection.classList.remove('hidden');
        clearRatesError(group);
        group.ratesStatus.textContent = `${rates.length} layanan tersedia`;
        group.ratesHint.textContent = `Pilih layanan kurir untuk ${group.label}.`;

        rates.forEach((rate) => {
            const label = document.createElement('label');
            label.dataset.shippingRate = 'true';
            label.className = 'flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3.5 py-3 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:gap-4 sm:px-4';

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = `${group.type}_shipping_rate_selection`;
            radio.value = `${rate.courier_code || ''}:${rate.service_code || ''}`;
            radio.className = 'h-4 w-4 shrink-0 accent-[#AE7C18]';

            const content = document.createElement('div');
            content.className = 'min-w-0 flex-1';

            const topRow = document.createElement('div');
            topRow.className = 'flex items-start justify-between gap-3';

            const serviceWrapper = document.createElement('div');
            serviceWrapper.className = 'flex min-w-0 items-center gap-3';

            const logoWrapper = document.createElement('div');
            logoWrapper.className = 'flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-100 bg-white';

            const courierNameText = rate.courier_name || rate.courier_code || 'Kurir';
            const logoUrl = { jnt: config.jntLogo, lion: config.lionLogo }[rate.courier_code];
            if (logoUrl) {
                const logo = document.createElement('img');
                logo.src = logoUrl;
                logo.alt = courierNameText;
                logo.className = 'h-full w-full object-contain p-1.5';
                logo.onerror = () => {
                    logoWrapper.innerHTML = '';
                    const fallback = document.createElement('span');
                    fallback.className = 'text-[10px] font-bold text-gray-500';
                    fallback.textContent = courierNameText.substring(0, 3).toUpperCase();
                    logoWrapper.appendChild(fallback);
                };
                logoWrapper.appendChild(logo);
            } else {
                const fallback = document.createElement('span');
                fallback.className = 'text-[10px] font-bold text-gray-500';
                fallback.textContent = courierNameText.substring(0, 3).toUpperCase();
                logoWrapper.appendChild(fallback);
            }

            const serviceContent = document.createElement('div');
            serviceContent.className = 'min-w-0';
            const courierName = document.createElement('p');
            courierName.className = 'text-xs font-semibold text-slate-900 sm:text-sm';
            courierName.textContent = courierNameText;
            const serviceName = document.createElement('p');
            serviceName.className = 'mt-0.5 text-[10px] font-medium text-gray-500 sm:text-xs';
            serviceName.textContent = rate.service_name || rate.service_code || 'Layanan';
            serviceContent.append(courierName, serviceName);
            serviceWrapper.append(logoWrapper, serviceContent);

            const price = document.createElement('p');
            price.className = 'shrink-0 text-xs font-bold text-[#AE7C18] sm:text-sm';
            price.textContent = formatRupiah(rate.price);
            topRow.append(serviceWrapper, price);

            const bottomRow = document.createElement('div');
            bottomRow.className = 'mt-1.5 flex flex-wrap gap-x-3 text-[10px] text-gray-400 sm:text-xs';
            if (rate.duration) {
                const duration = document.createElement('span');
                duration.textContent = `Estimasi ${rate.duration}`;
                bottomRow.appendChild(duration);
            }
            if (rate.service_type) {
                const serviceType = document.createElement('span');
                serviceType.textContent = rate.service_type;
                bottomRow.appendChild(serviceType);
            }

            content.append(topRow, bottomRow);
            label.append(radio, content);
            radio.addEventListener('change', () => {
                if (radio.checked) selectRate(group, rate, label);
            });
            group.ratesList.appendChild(label);
        });

        const oldCourierCode = group.courierCode.value;
        const oldServiceCode = group.courierServiceCode.value;
        if (oldCourierCode && oldServiceCode) {
            const restoredIndex = rates.findIndex((rate) =>
                rate.courier_code === oldCourierCode && rate.service_code === oldServiceCode
            );
            if (restoredIndex >= 0) {
                const radio = group.ratesList.querySelectorAll('input[type="radio"]')[restoredIndex];
                if (radio) {
                    radio.checked = true;
                    selectRate(group, rates[restoredIndex], radio.closest('label'));
                }
            }
        }
    }

    async function loadRates(group) {
        const combined = getFulfillmentMode() === 'combined';

        if (getShippingMethod(group) !== 'Kurir') return;
        if (combined && group.type !== 'pre_order') return;

        const postalCode = postalInput.value.trim();
        if (!/^\d{5,10}$/.test(postalCode)) {
            group.ratesSection.classList.add('hidden');
            group.ratesList.innerHTML = '';
            clearRatesError(group);
            group.ratesStatus.textContent = '';
            group.ratesHint.textContent = 'Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.';
            resetGroupRateSelection(group);
            return;
        }

        const currentRequest = ++group.requestSequence;
        group.ratesSection.classList.remove('hidden');
        group.ratesList.innerHTML = '';
        clearRatesError(group);
        group.ratesStatus.textContent = 'Menghitung...';
        group.ratesHint.textContent = combined
            ? 'Menghitung ongkir untuk seluruh pesanan Ready Stock dan Pre-order.'
            : `Sedang menghitung ongkir ${group.label}.`;

        resetGroupRateSelection(group);

        try {
            const response = await fetch(config.shippingRatesUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                    Accept: 'application/json'
                },
                body: JSON.stringify({
                    shipping_postal_code: postalCode,
                    shipping_latitude: latitudeInput?.value || null,
                    shipping_longitude: longitudeInput?.value || null,
                    group_type: combined ? 'combined' : group.type
                })
            });

            const data = await response.json();
            if (currentRequest !== group.requestSequence) return;

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal mengambil pilihan pengiriman.');
            }

            renderRates(group, data.data || []);

            if (combined) {
                group.ratesHint.textContent =
                    'Tarif berlaku untuk seluruh pesanan Ready Stock dan Pre-order dalam satu pengiriman.';
            }
        } catch (error) {
            if (currentRequest !== group.requestSequence) return;

            group.ratesSection.classList.remove('hidden');
            group.ratesList.innerHTML = '';
            resetGroupRateSelection(group);
            group.ratesStatus.textContent = '';
            showRatesError(group, error.message || 'Gagal mengambil pilihan pengiriman. Silakan coba lagi.');
            group.ratesHint.textContent = 'Periksa kembali kode pos dan alamat tujuan.';
        }
    }

    function updateShippingMethodUI(group) {
        const combined = getFulfillmentMode() === 'combined';
        const method = getShippingMethod(group);
        const isPickup = method === 'Ambil di Tempat';
        const isCourier = method === 'Kurir';
        const isCombinedReady = combined && group.type === 'ready';
        const showPickup = isPickup && !isCombinedReady;
        const showRates = isCourier && (!combined || group.type === 'pre_order');

        group.pickupSection.classList.toggle('hidden', !showPickup);

        [group.pickupDate, group.pickupTimeStart, group.pickupTimeEnd].forEach((input) => {
            input.disabled = !showPickup;
            input.required = showPickup;
        });

        group.courierCode.disabled = !isCourier;
        group.courierServiceCode.disabled = !isCourier;

        group.requestSequence++;
        group.ratesList.innerHTML = '';
        group.ratesSection.classList.toggle('hidden', !showRates);
        clearRatesError(group);
        group.ratesStatus.textContent = '';
        clearPickupError(group);

        if (isPickup) {
            if (showPickup) {
                applyPickupDateRules(group);
            }

            group.courierCode.value = '';
            group.courierServiceCode.value = '';
            group.selectedRate = null;
            group.cost = 0;
            group.costElement.textContent = 'Rp 0';
            group.costElement.className = 'text-[10px] font-semibold text-[#AE7C18] sm:text-xs';
            group.ratesHint.textContent = 'Tidak ada biaya kurir untuk pengambilan di tempat.';
            updateTotals();

            if (showPickup) {
                checkPickupDateAvailability(group);
            }
            return;
        }

        group.cost = null;
        group.selectedRate = null;
        group.costElement.textContent = 'Akan dihitung';
        group.costElement.className = 'text-[10px] text-gray-400 sm:text-xs';
        group.ratesHint.textContent = combined
            ? 'Pilih satu layanan kurir untuk seluruh pesanan.'
            : 'Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.';

        updateTotals();

        if (isCourier && showRates && /^\d{5,10}$/.test(postalInput.value.trim())) {
            loadRates(group);
        } else if (!isCourier) {
            group.courierCode.value = '';
            group.courierServiceCode.value = '';
        }
    }

    function updatePaymentMethodUI() {
        const paymentMethod = form.querySelector('input[name="payment_method"]:checked')?.value;
        const isVa = paymentMethod === 'VA';
        vaBankSection.classList.toggle('hidden', !isVa);
        vaBankInputs.forEach((input) => { input.disabled = !isVa; });
    }

    groups.forEach((group) => {
        group.methodInputs.forEach((input) => {
            input.addEventListener('change', () => {
                if (getFulfillmentMode() === 'combined') {
                    syncCombinedMethods(input.value);
                    groups.forEach(updateShippingMethodUI);
                } else {
                    updateShippingMethodUI(group);
                }
            });
        });

        group.pickupDate.addEventListener('change', async () => {
            applyPickupDateRules(group);
            updatePickupTimeRules(group);
            clearPickupError(group);
            await checkPickupDateAvailability(group);
        });
        group.pickupTimeStart.addEventListener('change', () => {
            updatePickupTimeRules(group);
            clearPickupError(group);
        });
        group.pickupTimeEnd.addEventListener('change', () => {
            updatePickupTimeRules(group);
            clearPickupError(group);
        });
    });

    function updateFulfillmentModeDisplay() {
        const combined = getFulfillmentMode() === 'combined';
        const ready = getGroupByType('ready');
        const preOrder = getGroupByType('pre_order');

        groups.forEach((group) => {
            group.element.classList.toggle(
                'hidden',
                combined && group.type === 'ready'
            );

            if (group.modeBadge) {
                group.modeBadge.classList.toggle('hidden', combined);
            }

            if (group.titleElement) {
                group.titleElement.textContent =
                    combined && group.type === 'pre_order'
                        ? 'Pesanan Gabungan'
                        : group.label;
            }

            if (group.summaryElement) {
                if (combined && group.type === 'pre_order') {
                    group.summaryElement.textContent =
                        `${group.combinedItemCount} item · Subtotal ${formatRupiah(group.combinedSubtotal)}`;
                } else {
                    group.summaryElement.textContent =
                        group.summaryElement.dataset.separateSummary;
                }
            }

            const hint = group.ratesHint;
            if (hint && combined) {
                hint.textContent = group.type === 'pre_order'
                    ? 'Satu layanan kurir dan satu ongkir untuk seluruh pesanan.'
                    : 'Biaya pengiriman dihitung bersama dengan Pre-order.';
            }
        });

        if (combined && ready && preOrder) {
            const method = getShippingMethod(preOrder) || getShippingMethod(ready);
            if (method) syncCombinedMethods(method);
        }

        const readyRow = form.querySelector('[data-shipping-cost-row="ready"]');
        const preOrderRow = form.querySelector('[data-shipping-cost-row="pre_order"]');
        const preOrderLabel = preOrderRow?.querySelector('[data-shipping-cost-label]');

        if (readyRow) readyRow.classList.toggle('hidden', combined);

        if (preOrderLabel) {
            preOrderLabel.textContent = combined
                ? 'Pesanan Gabungan (ongkir)'
                : 'Pre-order (ongkir)';
        }

        if (ready && ready.costElement) {
            ready.costElement.textContent = combined
                ? 'Termasuk ongkir gabungan'
                : (ready.cost === null ? 'Pilih metode' : formatRupiah(ready.cost));
            ready.costElement.className = combined
                ? 'text-[10px] text-gray-400 sm:text-xs'
                : (ready.cost === null
                    ? 'text-[10px] text-gray-400 sm:text-xs'
                    : 'text-[10px] font-semibold text-[#AE7C18] sm:text-xs');
        }
    }

    fulfillmentModeInputs.forEach((input) => {
        input.addEventListener('change', () => {
            updateFulfillmentModeDisplay();

            groups.forEach(updateShippingMethodUI);
        });
    });

    paymentMethodInputs.forEach((input) => input.addEventListener('change', updatePaymentMethodUI));

    postalInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            groups.filter((group) => getShippingMethod(group) === 'Kurir' && (getFulfillmentMode() !== 'combined' || group.type === 'pre_order')).forEach(loadRates);
        }, 700);
    });
    postalInput.addEventListener('change', () => {
        clearTimeout(debounceTimer);
        groups.filter((group) => getShippingMethod(group) === 'Kurir' && (getFulfillmentMode() !== 'combined' || group.type === 'pre_order')).forEach(loadRates);
    });

    form.addEventListener('submit', async (event) => {
        // Validasi seluruh kelompok dahulu; hanya kirim satu form/invoice setelah semuanya valid.
        event.preventDefault();
        const paymentMethod = form.querySelector('input[name="payment_method"]:checked')?.value;
        if (!paymentMethod) {
            event.preventDefault();
            form.querySelector('input[name="payment_method"]')?.reportValidity();
            return;
        }

        const combined = getFulfillmentMode() === 'combined';

        if (combined) {
            const readyMethod = getShippingMethod(getGroupByType('ready'));
            const preOrderMethod = getShippingMethod(getGroupByType('pre_order'));

            if (!readyMethod || readyMethod !== preOrderMethod) {
                alert('Pengiriman gabungan harus menggunakan metode yang sama untuk Ready Stock dan Pre-order.');
                return;
            }
        }

        for (const group of groups) {
            const method = getShippingMethod(group);

            if (!method) {
                group.methodInputs[0]?.reportValidity();
                return;
            }

            if (method === 'Kurir' && (!group.courierCode.value || !group.courierServiceCode.value)) {
                const rateGroup = combined ? getGroupByType('pre_order') : group;
                rateGroup.ratesSection.classList.remove('hidden');
                showRatesError(rateGroup, combined
                    ? 'Silakan pilih layanan kurir untuk seluruh pesanan.'
                    : `Silakan pilih layanan kurir untuk ${group.label} terlebih dahulu.`);
                rateGroup.ratesHint.textContent = 'Pilih salah satu layanan pengiriman sebelum membuat pesanan.';
                rateGroup.ratesSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (method === 'Ambil di Tempat') {
                if (combined && group.type === 'ready') {
                    continue;
                }

                const pickupError = validatePickupSchedule(group);
                if (pickupError) {
                    showPickupError(group, pickupError);
                    return;
                }

                const availability = await checkPickupDateAvailability(group);
                if (!availability) {
                    group.pickupSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }
        }
        form.submit();
    });

    updatePaymentMethodUI();
    updateFulfillmentModeDisplay();
    groups.forEach((group) => {
        const method = getShippingMethod(group);
        if (method) {
            updateShippingMethodUI(group);
        } else {
            group.pickupSection.classList.add('hidden');
            group.ratesSection.classList.add('hidden');
            group.costElement.textContent = 'Pilih metode';
        }
    });
    updateTotals();
});
