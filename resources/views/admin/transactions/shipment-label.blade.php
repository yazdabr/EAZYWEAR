@php
    $legacyLabel = $legacyLabel ?? false;
    $fulfillmentLabel = $fulfillmentLabel ?? false;

    if ($legacyLabel) {
        $transaction->loadMissing([
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
        ]);
    }

    $shipment->loadMissing('fulfillments.items.productVariant.product');

    $fulfillmentItems = $legacyLabel
        ? $transaction->items->values()
        : ($fulfillmentLabel
            ? $labelFulfillment->items->values()
            : $shipment->fulfillments
                ->flatMap(fn ($fulfillment) => $fulfillment->items)
                ->values());

    $totalQuantity = (int) $fulfillmentItems->sum(fn ($item) => max(0, (int) $item->qty));
    $totalWeightGrams = (int) $fulfillmentItems->sum(
        fn ($item) => max(0, (int) $item->weight) * max(0, (int) $item->qty)
    );

    $trackingNumber = $shipment->biteship_waybill_id
        ?: ($shipment->tracking_number ?: $shipment->biteship_tracking_id);

    $invoiceNumber = $transaction->invoice_number ?: ('TRANSAKSI-' . $transaction->id);
    $shipmentReference = $legacyLabel ? $invoiceNumber : ($invoiceNumber . ($fulfillmentLabel ? '-FULFILLMENT-' . $labelFulfillment->id : '-SHIPMENT-' . $shipment->id));

    $courierCode = strtolower(trim((string) ($shipment->courier_code ?: '')));
    $courierLabel = $shipment->courier ?: ($shipment->courier_code ?: 'Kurir');
    $courierLogo = null;

    if (str_contains($courierCode, 'jnt') || str_contains($courierCode, 'j&t')) {
        $courierLogo = asset('images/shipping/jnt.svg');
    } elseif (str_contains($courierCode, 'lion')) {
        $courierLogo = asset('images/shipping/lion.svg');
    }

    $serviceCode = trim((string) ($shipment->courier_service_code ?: ''));
    $serviceLabel = $serviceCode !== ''
        ? ucwords(str_replace(['_', '-'], ' ', $serviceCode))
        : '—';

    $recipientName = $shipment->shipping_name ?: ($transaction->shipping_name ?? '—');
    $recipientPhone = $shipment->shipping_phone ?: ($transaction->shipping_phone ?? '—');
    $recipientAddress = $shipment->shipping_address ?: ($transaction->shipping_address ?? '—');
    $recipientLocality = collect([
        $shipment->shipping_district ?: ($transaction->shipping_district ?? ''),
        $shipment->shipping_city ?: ($transaction->shipping_city ?? ''),
        $shipment->shipping_province ?: ($transaction->shipping_province ?? ''),
        $shipment->shipping_postal_code ?: ($transaction->shipping_postal_code ?? ''),
    ])->filter(fn ($value) => trim((string) $value) !== '')->implode(', ');

    $originName = data_get($origin ?? [], 'contact_name', config('biteship.origin.contact_name', 'Eazywear'));
    $originPhone = data_get($origin ?? [], 'contact_phone', config('biteship.origin.contact_phone', '—'));
    $originAddress = data_get($origin ?? [], 'address', config('biteship.origin.address', '—'));
    $originPostalCode = data_get($origin ?? [], 'postal_code', config('biteship.origin.postal_code', ''));

    $originAddressLine = collect([$originAddress, $originPostalCode])
        ->filter(fn ($value) => trim((string) $value) !== '')
        ->implode(', ');

    $shippingCost = (int) ($shipment->shipping_cost ?? 0);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label Pengiriman {{ $invoiceNumber }}</title>

    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #e8e8e8;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            width: 100mm;
            margin: 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        .toolbar button {
            padding: 7px 12px;
            border: 0;
            border-radius: 4px;
            background: #111827;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .label {
            width: 100mm;
            height: 150mm;
            margin: 0 auto 10px;
            padding: 3mm;
            background: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-size: 6.5pt;
            line-height: 1.15;
        }

        .top {
            height: 12mm;
            flex: 0 0 12mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3mm;
            border-bottom: 1px solid #111;
            padding: 0 0 1.5mm;
        }

        .brand-logo,
        .platform-logo {
            display: block;
            height: 7mm;
            object-fit: contain;
            align-self: center;
        }

        .brand-logo {
            width: 31mm;
            object-position: left center;
        }

        .platform-logo {
            width: 25mm;
            object-position: right center;
        }

        .courier {
            min-height: 10mm;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2mm;
            padding: 1.2mm 0;
            border-bottom: 1px solid #111;
        }

        .courier-logo {
            display: block;
            width: 19mm;
            height: 6mm;
            object-fit: contain;
            object-position: left center;
            margin-top: .5mm;
        }

        .courier-caption,
        .caption {
            font-size: 5.8pt;
            font-weight: 700;
        }

        .courier-name {
            max-width: 36mm;
            text-align: right;
            font-size: 7pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .tracking {
            flex: 0 0 auto;
            padding: 1.7mm 0;
            border-bottom: 1px solid #111;
        }

        .tracking-number {
            margin-top: .7mm;
            font-size: 10pt;
            line-height: 1.05;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .tracking-note {
            margin-top: .7mm;
            font-size: 5.5pt;
            line-height: 1.1;
        }

        .meta {
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #111;
        }

        .meta-cell {
            min-width: 0;
            padding: 1.3mm 1.2mm 1.3mm 0;
        }

        .meta-cell:nth-child(odd) {
            border-right: 1px solid #aaa;
        }

        .meta-cell:nth-child(even) {
            padding-left: 2mm;
        }

        .meta-label {
            display: block;
            font-size: 5.5pt;
            margin-bottom: .5mm;
        }

        .meta-value {
            display: block;
            font-size: 6.5pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .section {
            flex: 0 0 auto;
            padding: 1.5mm 0;
            border-bottom: 1px solid #111;
            min-width: 0;
        }

        .section-title {
            font-size: 6pt;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .8mm;
        }

        .address-name {
            font-size: 7pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .address-phone {
            margin-top: .4mm;
            font-size: 6.3pt;
            overflow-wrap: anywhere;
        }

        .address-text {
            margin-top: .6mm;
            font-size: 6pt;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .items {
            flex: 1 1 auto;
            min-height: 10mm;
            max-height: 23mm;
            overflow: hidden;
            padding: 1.5mm 0;
            border-bottom: 1px solid #111;
        }

        .item-row {
            display: grid;
            grid-template-columns: 8mm 1fr;
            gap: 1mm;
            padding: .6mm 0;
            font-size: 6pt;
            line-height: 1.15;
        }

        .item-qty {
            font-weight: 700;
            white-space: nowrap;
        }

        .item-name {
            overflow-wrap: anywhere;
        }

        .empty-items {
            font-size: 6pt;
        }

        .reference {
            flex: 0 0 auto;
            padding: 1.4mm 0;
            border-bottom: 1px solid #111;
        }

        .reference-number {
            margin-top: .5mm;
            font-size: 6.5pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .footer {
            flex: 0 0 auto;
            padding-top: 1.3mm;
            text-align: center;
            font-size: 5.5pt;
            line-height: 1.15;
        }

        .footer strong {
            display: block;
            font-size: 6pt;
        }

        @media print {
            html,
            body {
                width: 100mm;
                height: 150mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .label {
                width: 100mm;
                height: 150mm;
                margin: 0;
                padding: 3mm;
                page-break-after: avoid;
                page-break-inside: avoid;
                break-after: avoid-page;
                break-inside: avoid-page;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <span>Label internal · 100 × 150 mm</span>
        <button type="button" onclick="window.print()">Cetak Label</button>
    </div>

    <main class="label">
        <header class="top">
            <img class="brand-logo"
                 src="{{ asset('images/shipping/logoeazywear.svg') }}"
                 alt="Eazywear">
            <img class="platform-logo"
                 src="{{ asset('images/shipping/logobiteship.svg') }}"
                 alt="Biteship">
        </header>

        <section class="courier">
            <div>
                <div class="courier-caption">KURIR PENGIRIMAN</div>
                @if($courierLogo)
                    <img class="courier-logo" src="{{ $courierLogo }}" alt="{{ $courierLabel }}">
                @else
                    <div class="courier-name" style="text-align:left">{{ $courierLabel }}</div>
                @endif
            </div>
            <div class="courier-name">{{ $courierLabel }}</div>
        </section>

        <section class="tracking">
            <div class="caption">{{ $trackingNumber ? 'NOMOR RESI' : 'NOMOR RESI BELUM TERSEDIA' }}</div>
            <div class="tracking-number">{{ $trackingNumber ?: 'BELUM TERSEDIA' }}</div>
            @unless($trackingNumber)
                <div class="tracking-note">
                    Resi belum tersedia. Label ini merupakan label internal, bukan airwaybill resmi kurir.
                </div>
            @endunless
        </section>

        <section class="meta">
            <div class="meta-cell">
                <span class="meta-label">Ongkos Kirim</span>
                <span class="meta-value">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Jenis Layanan</span>
                <span class="meta-value">{{ $serviceLabel }}</span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Jumlah Barang</span>
                <span class="meta-value">{{ $totalQuantity }} Pcs</span>
            </div>
            <div class="meta-cell">
                <span class="meta-label">Total Berat</span>
                <span class="meta-value">
                    {{ $totalWeightGrams > 0 ? number_format($totalWeightGrams / 1000, 2, ',', '.') . ' Kg' : '—' }}
                </span>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Alamat Penerima</div>
            <div class="address-name">{{ $recipientName }}</div>
            <div class="address-phone">{{ $recipientPhone }}</div>
            <div class="address-text">
                {{ $recipientAddress }}@if($recipientLocality !== ''), {{ $recipientLocality }}@endif
            </div>
        </section>

        <section class="section">
            <div class="section-title">Alamat Pengirim</div>
            <div class="address-name">{{ $originName ?: 'Eazywear' }}</div>
            <div class="address-phone">{{ $originPhone ?: '—' }}</div>
            <div class="address-text">{{ $originAddressLine ?: 'Alamat pengirim belum dikonfigurasi' }}</div>
        </section>

        <section class="items">
            <div class="section-title">Jenis Barang</div>
            @forelse($fulfillmentItems as $item)
                @php
                    $productName = $item->productVariant?->product?->name
                        ?? $item->custom_name
                        ?? 'Produk Eazywear';
                @endphp
                <div class="item-row">
                    <div class="item-qty">{{ max(0, (int) $item->qty) }}x</div>
                    <div class="item-name">{{ $productName }}</div>
                </div>
            @empty
                <div class="empty-items">Rincian barang belum tersedia.</div>
            @endforelse
        </section>

        <section class="reference">
            <div class="caption">REFERENCE NUMBER</div>
            <div class="reference-number">{{ $shipmentReference }}</div>
            <div class="tracking-note">Invoice: {{ $invoiceNumber }}</div>
        </section>

        <footer class="footer">
            <strong>Pengiriman melalui platform Biteship</strong>
            biteship.com · Label internal Eazywear
        </footer>
    </main>
</body>
</html>
