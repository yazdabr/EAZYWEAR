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

    const postalInput = document.getElementById(
        'shipping_postal_code'
    );

    const latitudeInput = document.getElementById(
        'shipping_latitude'
    );

    const longitudeInput = document.getElementById(
        'shipping_longitude'
    );

    const ratesSection = document.getElementById(
        'shipping-rates-section'
    );

    const ratesList = document.getElementById(
        'shipping-rates-list'
    );

    const ratesStatus = document.getElementById(
        'shipping-rates-status'
    );

    const ratesError = document.getElementById(
        'shipping-rates-error'
    );

    const ratesHint = document.getElementById(
        'shipping-rates-hint'
    );

    const pickupSection = document.getElementById(
        'pickup-section'
    );

    const pickupDateInput = document.getElementById(
        'pickup_date'
    );

    const pickupTimeStartInput = document.getElementById(
        'pickup_time_start'
    );

    const pickupTimeEndInput = document.getElementById(
        'pickup_time_end'
    );

    const pickupValidationError = document.getElementById(
        'pickup-validation-error'
    );

    const shippingCost = document.getElementById(
        'shipping-cost'
    );

    const totalAmount = document.getElementById(
        'total-amount'
    );

    const totalNote = document.getElementById(
        'total-note'
    );

    const courierCodeInput = document.getElementById(
        'courier_code'
    );

    const courierServiceCodeInput = document.getElementById(
        'courier_service_code'
    );

    const vaBankSection = document.getElementById(
        'va-bank-section'
    );

    const paymentMethodInputs = form.querySelectorAll(
        'input[name="payment_method"]'
    );

    const vaBankInputs = form.querySelectorAll(
        'input[name="va_bank"]'
    );

    /*
    |--------------------------------------------------------------------------
    | Required Elements
    |--------------------------------------------------------------------------
    */

    if (
        !postalInput ||
        !vaBankSection ||
        paymentMethodInputs.length === 0 ||
        vaBankInputs.length === 0 ||
        !ratesSection ||
        !ratesList ||
        !ratesStatus ||
        !ratesError ||
        !ratesHint ||
        !shippingCost ||
        !totalAmount ||
        !totalNote ||
        !courierCodeInput ||
        !courierServiceCodeInput ||
        !pickupSection ||
        !pickupDateInput ||
        !pickupTimeStartInput ||
        !pickupTimeEndInput ||
        !pickupValidationError
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout Configuration
    |--------------------------------------------------------------------------
    */

    const config = {
        shippingRatesUrl:
            form.dataset.shippingRatesUrl,

        fulfillmentAvailabilityUrl:
            form.dataset.fulfillmentAvailabilityUrl,

        subtotal:
            Number(form.dataset.subtotal || 0),

        specialBatchStart:
            form.dataset.specialBatchStart,

        specialBatchEnd:
            form.dataset.specialBatchEnd,

        specialBatchCapacity:
            Number(
                form.dataset.specialBatchCapacity || 100
            ),

        pickupStartTime:
            form.dataset.pickupStartTime,

        pickupEndTime:
            form.dataset.pickupEndTime,

        pickupSameDayCutoff:
            form.dataset.pickupSameDayCutoff,

        checkoutDate:
            form.dataset.checkoutDate,

        checkoutTime:
            form.dataset.checkoutTime,

        jntLogo:
            form.dataset.jntLogo,

        lionLogo:
            form.dataset.lionLogo
    };

    const pickupAvailabilityStatus =
        document.getElementById(
            'pickup-availability-status'
        );    

    const specialBatchStartDate =
        config.specialBatchStart;

    const specialBatchEndDate =
        config.specialBatchEnd;

    const specialBatchCapacity =
        config.specialBatchCapacity;

    const pickupStartTime =
        config.pickupStartTime;

    const pickupEndTime =
        config.pickupEndTime;

    const pickupSameDayCutoff =
        config.pickupSameDayCutoff;

    const checkoutDate =
        config.checkoutDate;

    const checkoutTime =
        config.checkoutTime;

    const subtotal =
        config.subtotal;

    let debounceTimer = null;

    let requestSequence = 0;

    let selectedRate = null;

    let pickupAvailabilityRequest = 0;

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value) {
        return (
            'Rp ' +
            Number(value || 0).toLocaleString('id-ID')
        );
    }

    function addDays(dateString, days) {
        const date = new Date(
            `${dateString}T00:00:00`
        );

        date.setDate(
            date.getDate() + days
        );

        const year =
            date.getFullYear();

        const month =
            String(
                date.getMonth() + 1
            ).padStart(2, '0');

        const day =
            String(
                date.getDate()
            ).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatDisplayDate(dateString) {
        if (!dateString) {
            return '';
        }

        const [
            year,
            month,
            day
        ] = dateString.split('-');

        const months = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        ];

        return (
            `${day} ` +
            `${months[Number(month) - 1]} ` +
            `${year}`
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Method
    |--------------------------------------------------------------------------
    */

    function updatePaymentMethodUI() {
        const paymentMethod =
            form.querySelector(
                'input[name="payment_method"]:checked'
            )?.value;

        const isVa =
            paymentMethod === 'VA';

        vaBankSection.classList.toggle(
            'hidden',
            !isVa
        );

        vaBankInputs.forEach(
            function (input) {
                input.disabled = !isVa;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Shipping Rate Helpers
    |--------------------------------------------------------------------------
    */

    function resetShippingSelection() {
        selectedRate = null;

        courierCodeInput.value = '';
        courierServiceCodeInput.value = '';

        shippingCost.textContent =
            'Akan dihitung';

        shippingCost.className =
            'text-[10px] text-gray-400 sm:text-xs';

        totalAmount.textContent =
            formatRupiah(subtotal);

        totalNote.textContent =
            'Belum termasuk ongkir';
    }

    function showRatesError(message) {
        ratesError.textContent =
            message;

        ratesError.classList.remove(
            'hidden'
        );

        ratesStatus.textContent = '';
    }

    function hideRatesError() {
        ratesError.textContent = '';

        ratesError.classList.add(
            'hidden'
        );
    }

    function selectRate(
        rate,
        labelElement
    ) {
        hideRatesError();

        selectedRate = rate;

        courierCodeInput.value =
            rate.courier_code || '';

        courierServiceCodeInput.value =
            rate.service_code || '';

        shippingCost.textContent =
            formatRupiah(rate.price);

        shippingCost.className =
            'text-[10px] font-semibold text-[#AE7C18] sm:text-xs';

        totalAmount.textContent =
            formatRupiah(
                subtotal +
                Number(rate.price || 0)
            );

        totalNote.textContent =
            'Termasuk ongkir';

        ratesList
            .querySelectorAll(
                '[data-shipping-rate]'
            )
            .forEach(
                function (element) {
                    element.classList.remove(
                        'border-[#AE7C18]',
                        'bg-[#AE7C18]/5'
                    );

                    element.classList.add(
                        'border-gray-200'
                    );
                }
            );

        labelElement.classList.remove(
            'border-gray-200'
        );

        labelElement.classList.add(
            'border-[#AE7C18]',
            'bg-[#AE7C18]/5'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Special Batch / Pickup Date
    |--------------------------------------------------------------------------
    */

    function isSpecialBatchCheckout() {
        /*
         * Selama tanggal checkout belum melewati
         * akhir special batch, order masih mengikuti
         * jadwal special batch.
         *
         * Contoh:
         *
         * 07 Okt -> special batch
         * 20 Okt -> special batch
         * 29 Okt -> special batch
         * 30 Okt -> special batch
         * 03 Nov -> special batch
         * 04 Nov -> normal
         */
        return (
            checkoutDate <=
            specialBatchEndDate
        );
    }

    function getMinimumPickupDate() {
        if (isSpecialBatchCheckout()) {
            /*
             * Sebelum batch:
             * minimum = 30 Okt.
             *
             * Saat batch sudah berjalan:
             * minimum = hari checkout.
             */
            return checkoutDate >
                specialBatchStartDate
                ? checkoutDate
                : specialBatchStartDate;
        }

        /*
         * Normal pickup:
         *
         * <= 14:00
         * -> hari ini
         *
         * > 14:00
         * -> besok
         */
        return checkoutTime <=
            pickupSameDayCutoff
            ? checkoutDate
            : addDays(
                checkoutDate,
                1
            );
    }

    function applyPickupDateRules() {
        const minimumDate =
            getMinimumPickupDate();

        pickupDateInput.min =
            minimumDate;

        if (isSpecialBatchCheckout()) {
            pickupDateInput.max =
                specialBatchEndDate;
        } else {
            pickupDateInput.removeAttribute(
                'max'
            );
        }

        if (
            pickupDateInput.value &&
            pickupDateInput.value <
                minimumDate
        ) {
            pickupDateInput.value =
                minimumDate;
        }

        if (
            isSpecialBatchCheckout() &&
            pickupDateInput.value &&
            pickupDateInput.value >
                specialBatchEndDate
        ) {
            pickupDateInput.value =
                minimumDate;
        }

        applyPickupTimeRules();
    }

    function updatePickupDateUI() {
        /*
        * UI tidak lagi menampilkan informasi batch khusus,
        * kapasitas, atau kuota.
        *
        * Aturan tanggal tetap diterapkan melalui:
        * - min
        * - max
        * - validasi submit
        */
    }

    /*
    |--------------------------------------------------------------------------
    | Pickup Time
    |--------------------------------------------------------------------------
    */

    function applyPickupTimeRules() {
        /*
         * Default:
         * 09:00 - 17:00
         */
        pickupTimeStartInput.min =
            pickupStartTime;

        pickupTimeStartInput.max =
            pickupEndTime;

        pickupTimeEndInput.min =
            pickupStartTime;

        pickupTimeEndInput.max =
            pickupEndTime;

        const selectedDate =
            pickupDateInput.value;

        /*
         * Normal pickup hari ini:
         * waktu mulai tidak boleh sebelum
         * waktu checkout.
         */
        if (
            !isSpecialBatchCheckout() &&
            selectedDate === checkoutDate
        ) {
            const minimumTime =
                checkoutTime >
                pickupStartTime
                    ? checkoutTime
                    : pickupStartTime;

            pickupTimeStartInput.min =
                minimumTime;

            pickupTimeEndInput.min =
                minimumTime;
        }

        /*
         * Waktu selesai minimal sama
         * dengan waktu mulai.
         */
        if (
            pickupTimeStartInput.value
        ) {
            pickupTimeEndInput.min =
                pickupTimeStartInput.value;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Pickup Validation
    |--------------------------------------------------------------------------
    */

    function showPickupValidationError(
        message
    ) {
        pickupValidationError.textContent =
            message;

        pickupValidationError.classList.remove(
            'hidden'
        );

        pickupSection.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }

    function clearPickupValidationError() {
        pickupValidationError.textContent =
            '';

        pickupValidationError.classList.add(
            'hidden'
        );
    }

    function validatePickupSchedule() {
        const pickupDate =
            pickupDateInput.value;

        const pickupStart =
            pickupTimeStartInput.value;

        const pickupEnd =
            pickupTimeEndInput.value;

        /*
         * Field wajib
         */
        if (
            !pickupDate ||
            !pickupStart ||
            !pickupEnd
        ) {
            return (
                'Silakan lengkapi tanggal dan waktu ' +
                'pengambilan terlebih dahulu.'
            );
        }

        const minimumDate =
            getMinimumPickupDate();

        /*
         * Tanggal tidak boleh sebelum
         * minimum.
         */
        if (
            pickupDate < minimumDate
        ) {
            return (
                `Tanggal pickup paling cepat ` +
                `${formatDisplayDate(minimumDate)}.`
            );
        }

        /*
         * Special batch hanya sampai
         * 3 November.
         */
        if (
            isSpecialBatchCheckout() &&
            pickupDate >
                specialBatchEndDate
        ) {
            return (
                `Untuk batch ini, pickup hanya tersedia ` +
                `sampai ${formatDisplayDate(specialBatchEndDate)}.`
            );
        }

        /*
         * Jam harus 09:00 - 17:00.
         */
        if (
            pickupStart <
                pickupStartTime ||
            pickupStart >
                pickupEndTime ||
            pickupEnd <
                pickupStartTime ||
            pickupEnd >
                pickupEndTime
        ) {
            return (
                `Jam pengambilan hanya tersedia antara ` +
                `${pickupStartTime} sampai ${pickupEndTime}.`
            );
        }

        /*
         * Mulai tidak boleh setelah selesai.
         */
        if (
            pickupStart >
            pickupEnd
        ) {
            return (
                'Waktu mulai tidak boleh lebih dari waktu selesai.'
            );
        }

        /*
         * Normal same-day pickup.
         */
        if (
            !isSpecialBatchCheckout() &&
            pickupDate === checkoutDate
        ) {
            /*
             * Setelah cutoff tidak boleh
             * pickup hari yang sama.
             */
            if (
                checkoutTime >
                pickupSameDayCutoff
            ) {
                return (
                    `Pickup hari yang sama hanya tersedia ` +
                    `sampai pukul ${pickupSameDayCutoff}. ` +
                    `Silakan pilih tanggal berikutnya.`
                );
            }

            /*
             * Waktu pickup tidak boleh
             * sudah lewat.
             */
            if (
                pickupStart <
                checkoutTime
            ) {
                return (
                    'Waktu mulai pickup sudah lewat. ' +
                    'Silakan pilih waktu yang masih tersedia ' +
                    'hari ini atau tanggal berikutnya.'
                );
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Shipping Method UI
    |--------------------------------------------------------------------------
    */

    function updateShippingMethodUI() {
        const shippingMethod =
            form.querySelector(
                'input[name="shipping_method"]:checked'
            )?.value;

        const isPickup =
            shippingMethod ===
            'Ambil di Tempat';

        pickupSection.classList.toggle(
            'hidden',
            !isPickup
        );

        if (isPickup) {
            /*
             * Terapkan aturan pickup setiap
             * kali user memilih pickup.
             */
            applyPickupDateRules();

            applyPickupTimeRules();

            requestSequence++;

            ratesSection.classList.add(
                'hidden'
            );

            ratesList.innerHTML = '';

            hideRatesError();

            ratesStatus.textContent =
                '';

            courierCodeInput.value =
                '';

            courierServiceCodeInput.value =
                '';

            selectedRate = null;

            shippingCost.textContent =
                'Rp 0';

            shippingCost.className =
                'text-[10px] font-semibold text-[#AE7C18] sm:text-xs';

            totalAmount.textContent =
                formatRupiah(subtotal);

            totalNote.textContent =
                'Pengambilan di tempat';

            ratesHint.textContent =
                `Pickup tersedia ${formatDisplayDate(specialBatchStartDate)}–` +
                `${formatDisplayDate(specialBatchEndDate)}. ` +
                `Jika tanggal yang dipilih sudah penuh, silakan pilih tanggal lainnya.`;

                        return;
                    }

        /*
         * Kurir
         */
        shippingCost.textContent =
            'Akan dihitung';

        shippingCost.className =
            'text-[10px] text-gray-400 sm:text-xs';

        totalAmount.textContent =
            formatRupiah(subtotal);

        totalNote.textContent =
            'Belum termasuk ongkir';

        ratesHint.textContent =
            'Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.';

        if (
            /^\d{5,10}$/.test(
                postalInput.value.trim()
            )
        ) {
            loadRates();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Render Shipping Rates
    |--------------------------------------------------------------------------
    */

    function renderRates(rates) {
        ratesList.innerHTML = '';

        resetShippingSelection();

        if (
            !Array.isArray(rates) ||
            rates.length === 0
        ) {
            ratesSection.classList.remove(
                'hidden'
            );

            ratesStatus.textContent =
                '';

            showRatesError(
                'Belum ada layanan pengiriman yang tersedia untuk alamat tersebut.'
            );

            ratesHint.textContent =
                'Coba periksa kembali kode pos atau titik lokasi penerima.';

            return;
        }

        ratesSection.classList.remove(
            'hidden'
        );

        hideRatesError();

        ratesStatus.textContent =
            rates.length +
            ' layanan tersedia';

        ratesHint.textContent =
            'Pilih salah satu layanan pengiriman yang tersedia.';

        rates.forEach(
            function (rate) {
                const label =
                    document.createElement(
                        'label'
                    );

                label.setAttribute(
                    'data-shipping-rate',
                    'true'
                );

                label.className =
                    'flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3.5 py-3 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:gap-4 sm:px-4';

                const radio =
                    document.createElement(
                        'input'
                    );

                radio.type = 'radio';

                radio.name =
                    'shipping_rate_selection';

                radio.value =
                    (rate.courier_code || '') +
                    ':' +
                    (rate.service_code || '');

                radio.className =
                    'h-4 w-4 shrink-0 accent-[#AE7C18]';

                const content =
                    document.createElement(
                        'div'
                    );

                content.className =
                    'min-w-0 flex-1';

                const topRow =
                    document.createElement(
                        'div'
                    );

                topRow.className =
                    'flex items-start justify-between gap-3';

                const serviceWrapper =
                    document.createElement(
                        'div'
                    );

                serviceWrapper.className =
                    'flex min-w-0 items-center gap-3';

                const logoWrapper =
                    document.createElement(
                        'div'
                    );

                logoWrapper.className =
                    'flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-100 bg-white';

                const logo =
                    document.createElement(
                        'img'
                    );

                logo.className =
                    'h-full w-full object-contain p-1.5';

                logo.alt =
                    rate.courier_name ||
                    rate.courier_code ||
                    'Kurir';

                const courierLogos = {
                    jnt: config.jntLogo,
                    lion: config.lionLogo
                };

                if (
                    courierLogos[
                        rate.courier_code
                    ]
                ) {
                    logo.src =
                        courierLogos[
                            rate.courier_code
                        ];

                    logo.onerror =
                        function () {
                            logoWrapper.innerHTML =
                                '';

                            const fallback =
                                document.createElement(
                                    'span'
                                );

                            fallback.className =
                                'text-[10px] font-bold text-gray-500';

                            fallback.textContent =
                                (
                                    rate.courier_name ||
                                    rate.courier_code ||
                                    'Kurir'
                                )
                                    .substring(
                                        0,
                                        3
                                    )
                                    .toUpperCase();

                            logoWrapper.appendChild(
                                fallback
                            );
                        };

                    logoWrapper.appendChild(
                        logo
                    );
                } else {
                    const fallback =
                        document.createElement(
                            'span'
                        );

                    fallback.className =
                        'text-[10px] font-bold text-gray-500';

                    fallback.textContent =
                        (
                            rate.courier_name ||
                            rate.courier_code ||
                            'Kurir'
                        )
                            .substring(
                                0,
                                3
                            )
                            .toUpperCase();

                    logoWrapper.appendChild(
                        fallback
                    );
                }

                const serviceContent =
                    document.createElement(
                        'div'
                    );

                serviceContent.className =
                    'min-w-0';

                const courierName =
                    document.createElement(
                        'p'
                    );

                courierName.className =
                    'text-xs font-semibold text-slate-900 sm:text-sm';

                courierName.textContent =
                    rate.courier_name ||
                    rate.courier_code ||
                    'Kurir';

                const serviceName =
                    document.createElement(
                        'p'
                    );

                serviceName.className =
                    'mt-0.5 text-[10px] font-medium text-gray-500 sm:text-xs';

                serviceName.textContent =
                    rate.service_name ||
                    rate.service_code ||
                    'Layanan';

                serviceContent.appendChild(
                    courierName
                );

                serviceContent.appendChild(
                    serviceName
                );

                serviceWrapper.appendChild(
                    logoWrapper
                );

                serviceWrapper.appendChild(
                    serviceContent
                );

                const price =
                    document.createElement(
                        'p'
                    );

                price.className =
                    'shrink-0 text-xs font-bold text-[#AE7C18] sm:text-sm';

                price.textContent =
                    formatRupiah(
                        rate.price
                    );

                topRow.appendChild(
                    serviceWrapper
                );

                topRow.appendChild(
                    price
                );

                const bottomRow =
                    document.createElement(
                        'div'
                    );

                bottomRow.className =
                    'mt-1.5 flex flex-wrap gap-x-3 text-[10px] text-gray-400 sm:text-xs';

                if (rate.duration) {
                    const duration =
                        document.createElement(
                            'span'
                        );

                    duration.textContent =
                        'Estimasi ' +
                        rate.duration;

                    bottomRow.appendChild(
                        duration
                    );
                }

                if (rate.service_type) {
                    const serviceType =
                        document.createElement(
                            'span'
                        );

                    serviceType.textContent =
                        rate.service_type;

                    bottomRow.appendChild(
                        serviceType
                    );
                }

                content.appendChild(
                    topRow
                );

                content.appendChild(
                    bottomRow
                );

                label.appendChild(
                    radio
                );

                label.appendChild(
                    content
                );

                radio.addEventListener(
                    'change',
                    function () {
                        if (
                            radio.checked
                        ) {
                            selectRate(
                                rate,
                                label
                            );
                        }
                    }
                );

                ratesList.appendChild(
                    label
                );
            }
        );

        /*
         * Restore old selected rate.
         */
        const oldCourierCode =
            courierCodeInput.value;

        const oldServiceCode =
            courierServiceCodeInput.value;

        if (
            oldCourierCode &&
            oldServiceCode
        ) {
            const restoredRate =
                rates.find(
                    function (rate) {
                        return (
                            rate.courier_code ===
                                oldCourierCode &&
                            rate.service_code ===
                                oldServiceCode
                        );
                    }
                );

            if (restoredRate) {
                const radios =
                    ratesList.querySelectorAll(
                        'input[name="shipping_rate_selection"]'
                    );

                rates.forEach(
                    function (
                        rate,
                        index
                    ) {
                        if (
                            rate.courier_code ===
                                oldCourierCode &&
                            rate.service_code ===
                                oldServiceCode &&
                            radios[index]
                        ) {
                            radios[index].checked =
                                true;

                            selectRate(
                                restoredRate,
                                radios[index].closest(
                                    'label'
                                )
                            );
                        }
                    }
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Load Shipping Rates
    |--------------------------------------------------------------------------
    */

    async function loadRates() {
        const shippingMethod =
            form.querySelector(
                'input[name="shipping_method"]:checked'
            )?.value;

        if (
            shippingMethod !== 'Kurir'
        ) {
            return;
        }

        const postalCode =
            postalInput.value.trim();

        if (
            !/^\d{5,10}$/.test(
                postalCode
            )
        ) {
            ratesSection.classList.add(
                'hidden'
            );

            ratesList.innerHTML = '';

            hideRatesError();

            ratesStatus.textContent =
                '';

            ratesHint.textContent =
                'Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.';

            resetShippingSelection();

            return;
        }

        const currentRequest =
            ++requestSequence;

        ratesSection.classList.remove(
            'hidden'
        );

        ratesList.innerHTML = '';

        hideRatesError();

        ratesStatus.textContent =
            'Menghitung...';

        ratesHint.textContent =
            'Sedang mengambil pilihan layanan pengiriman.';

        resetShippingSelection();

        try {
            const response =
                await fetch(
                    config.shippingRatesUrl,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'input[name="_token"]'
                                )?.value || '',

                            'Accept':
                                'application/json'
                        },

                        body: JSON.stringify({
                            shipping_postal_code:
                                postalCode,

                            shipping_latitude:
                                latitudeInput?.value ||
                                null,

                            shipping_longitude:
                                longitudeInput?.value ||
                                null
                        })
                    }
                );

            const data =
                await response.json();

            if (
                currentRequest !==
                requestSequence
            ) {
                return;
            }

            if (
                !response.ok ||
                !data.success
            ) {
                throw new Error(
                    data.message ||
                    'Gagal mengambil pilihan pengiriman.'
                );
            }

            renderRates(
                data.data || []
            );
        } catch (error) {
            if (
                currentRequest !==
                requestSequence
            ) {
                return;
            }

            ratesSection.classList.remove(
                'hidden'
            );

            ratesList.innerHTML =
                '';

            resetShippingSelection();

            ratesStatus.textContent =
                '';

            showRatesError(
                error.message ||
                'Gagal mengambil pilihan pengiriman. Silakan coba lagi.'
            );

            ratesHint.textContent =
                'Periksa kembali kode pos dan alamat tujuan.';
        }
    }

    async function checkPickupDateAvailability() {
        const pickupDate =
            pickupDateInput.value;

        if (
            !pickupDate ||
            !isSpecialBatchCheckout() ||
            !pickupAvailabilityStatus
        ) {
            if (pickupAvailabilityStatus) {
                pickupAvailabilityStatus.textContent = '';
                pickupAvailabilityStatus.classList.add('hidden');
            }

            return true;
        }

        const currentRequest =
            ++pickupAvailabilityRequest;

        pickupAvailabilityStatus.textContent =
            'Memeriksa ketersediaan tanggal...';

        pickupAvailabilityStatus.className =
            'mt-1.5 text-[10px] leading-4 text-gray-400 sm:text-xs';

        try {
            const url =
                new URL(
                    config.fulfillmentAvailabilityUrl,
                    window.location.origin
                );

            url.searchParams.set(
                'date',
                pickupDate
            );

            const response =
                await fetch(url, {
                    method: 'GET',

                    headers: {
                        'Accept':
                            'application/json'
                    }
                });

            const data =
                await response.json();

            if (
                currentRequest !==
                pickupAvailabilityRequest
            ) {
                return true;
            }

            if (
                !response.ok ||
                !data.success
            ) {
                throw new Error(
                    data.message ||
                    'Gagal memeriksa ketersediaan tanggal.'
                );
            }

            if (!data.available) {
                pickupAvailabilityStatus.textContent =
                    data.message ||
                    'Tanggal pickup tersebut sudah penuh. Silakan pilih tanggal lain.';

                pickupAvailabilityStatus.className =
                    'mt-1.5 text-[10px] leading-4 text-red-600 sm:text-xs';

                return false;
            }

            pickupAvailabilityStatus.textContent =
                'Tanggal pickup masih tersedia.';

            pickupAvailabilityStatus.className =
                'mt-1.5 text-[10px] leading-4 text-green-600 sm:text-xs';

            return true;
        } catch (error) {
            if (
                currentRequest !==
                pickupAvailabilityRequest
            ) {
                return true;
            }

            pickupAvailabilityStatus.textContent =
                error.message ||
                'Ketersediaan tanggal belum dapat diperiksa.';

            pickupAvailabilityStatus.className =
                'mt-1.5 text-[10px] leading-4 text-red-600 sm:text-xs';

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Pickup Events
    |--------------------------------------------------------------------------
    */

    pickupDateInput.addEventListener(
        'change',
        async function () {
            applyPickupDateRules();
            applyPickupTimeRules();
            clearPickupValidationError();

            await checkPickupDateAvailability();
        }
    );

    pickupTimeStartInput.addEventListener(
        'change',
        function () {
            applyPickupTimeRules();

            clearPickupValidationError();
        }
    );

    pickupTimeEndInput.addEventListener(
        'change',
        function () {
            applyPickupTimeRules();

            clearPickupValidationError();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Payment Events
    |--------------------------------------------------------------------------
    */

    paymentMethodInputs.forEach(
        function (input) {
            input.addEventListener(
                'change',
                function () {
                    updatePaymentMethodUI();
                }
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Shipping Method Events
    |--------------------------------------------------------------------------
    */

    form
        .querySelectorAll(
            'input[name="shipping_method"]'
        )
        .forEach(
            function (input) {
                input.addEventListener(
                    'change',
                    function () {
                        updateShippingMethodUI();
                    }
                );
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Postal Code Events
    |--------------------------------------------------------------------------
    */

    postalInput.addEventListener(
        'input',
        function () {
            clearTimeout(
                debounceTimer
            );

            debounceTimer =
                setTimeout(
                    loadRates,
                    700
                );
        }
    );

    postalInput.addEventListener(
        'change',
        function () {
            clearTimeout(
                debounceTimer
            );

            loadRates();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Submit Validation
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function (event) {
            const paymentMethod =
                form.querySelector(
                    'input[name="payment_method"]:checked'
                )?.value;

            /*
             * Payment method wajib dipilih.
             */
            if (!paymentMethod) {
                event.preventDefault();

                const firstPaymentMethod =
                    form.querySelector(
                        'input[name="payment_method"]'
                    );

                if (
                    firstPaymentMethod
                ) {
                    firstPaymentMethod.reportValidity();
                }

                return;
            }

            const shippingMethod =
                form.querySelector(
                    'input[name="shipping_method"]:checked'
                )?.value;

            /*
             * Kurir harus memiliki
             * service yang dipilih.
             */
            if (
                shippingMethod ===
                    'Kurir' &&
                (
                    !courierCodeInput.value ||
                    !courierServiceCodeInput.value
                )
            ) {
                event.preventDefault();

                ratesSection.classList.remove(
                    'hidden'
                );

                showRatesError(
                    'Silakan pilih layanan pengiriman terlebih dahulu.'
                );

                ratesHint.textContent =
                    'Pilih salah satu layanan pengiriman sebelum membuat pesanan.';

                ratesSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return;
            }

            /*
             * Pickup validation.
             */
            if (
                shippingMethod ===
                'Ambil di Tempat'
            ) {
                const pickupError =
                    validatePickupSchedule();

                if (pickupError) {
                    event.preventDefault();

                    showPickupValidationError(
                        pickupError
                    );

                    return;
                }

                clearPickupValidationError();

                courierCodeInput.value =
                    '';

                courierServiceCodeInput.value =
                    '';
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Initial State
    |--------------------------------------------------------------------------
    */

    applyPickupDateRules();

    applyPickupTimeRules();

    updatePaymentMethodUI();

    updateShippingMethodUI();
});