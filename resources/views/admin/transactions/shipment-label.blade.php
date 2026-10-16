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

    $barcodeSvg = null;

    if (filled($trackingNumber) && preg_match('/^[A-Za-z0-9-]+$/', $trackingNumber)) {
        $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeSvg = $barcodeGenerator->getBarcode(
            $trackingNumber,
            $barcodeGenerator::TYPE_CODE_128,
            1.5,
            32
        );
    }

    $invoiceNumber = $transaction->invoice_number ?: ('TRANSAKSI-' . $transaction->id);
    $shipmentReference = $legacyLabel ? $invoiceNumber : ($invoiceNumber . ($fulfillmentLabel ? '-FULFILLMENT-' . $labelFulfillment->id : '-SHIPMENT-' . $shipment->id));

    $referenceBarcodeSvg = null;

    if (filled($shipmentReference) && preg_match('/^[A-Za-z0-9-]+$/', $shipmentReference)) {
        $referenceBarcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();

        $referenceBarcodeSvg = $referenceBarcodeGenerator->getBarcode(
            $shipmentReference,
            $referenceBarcodeGenerator::TYPE_CODE_128,
            1,
            24
        );
    }

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
            size: 80mm 100mm;
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
            width: 80mm;
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

        /* Wrapper luar dibuat tipis agar bingkai hitam mengisi sebagian besar area 80x100mm */
        .label-wrapper {
            width: 80mm;
            height: 100mm;
            margin: 0 auto 10px;
            padding: 2mm 2mm;
            background: #fff;
        }

        .label {
            width: 100%;
            height: 100%;
            padding: 1.5mm 2mm;
            background: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            font-size: 5.2pt;
            line-height: 1.05;
            border: 1.5px solid #111;
        }

        .top {
            height: 7mm;
            flex: 0 0 7mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3mm;
            border-bottom: 1px solid #111;
            margin-left: -2mm;
            margin-right: -2mm;
            padding-left: 2mm;
            padding-right: 2mm;
            padding-bottom: 1mm;
        }

        .brand-logo,
        .platform-logo {
            display: block;
            height: 6mm;
            object-fit: contain;
        }

        .brand-logo {
            width: 28mm;
            object-position: left center;
            align-self: flex-start;
            margin-top: -0.6mm;
        }

        .platform-logo {
            width: 22mm;
            object-position: right center;
            align-self: center;
        }

        .courier {
            min-height: 8mm;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2mm;
            padding: 1mm 0;
            border-bottom: 1px solid #111;
            margin-left: -2mm;
            margin-right: -2mm;
            padding-left: 2mm;
            padding-right: 2mm;
        }

        .courier-logo {
            display: block;
            width: 17mm;
            height: 5mm;
            object-fit: contain;
            object-position: left center;
            margin-top: .3mm;
        }

        .courier-caption,
        .caption {
            font-size: 5.2pt;
            font-weight: 700;
        }

        .courier-name {
            max-width: 32mm;
            text-align: right;
            font-size: 7pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .tracking {
            text-align: center;
            flex: 0 0 auto;
            padding: 0.6mm 0;
            border-bottom: 1px solid #111;
            margin-left: -2mm;
            margin-right: -2mm;
            padding-left: 2mm;
            padding-right: 2mm;
        }

        .tracking-number {
            text-align: center;
            margin-top: .4mm;
            font-size: 9.5pt;
            line-height: 1.05;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .tracking-note {
            margin-top: .4mm;
            font-size: 5pt;
            line-height: 1.1;
        }

        .tracking-barcode {
            margin-top: 0.6mm;
            width: 100%;
            text-align: center;
            overflow: hidden;
        }

        .tracking-barcode svg {
            display: block;
            width: auto;
            max-width: 100%;
            height: 7mm;
            margin: 0 auto;
        }

        .meta {
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #111;
            margin-left: -2mm;
            margin-right: -2mm;
            padding-left: 2mm;
            padding-right: 2mm;
        }

        .meta-cell {
            min-width: 0;
            padding: 0.5mm 0.8mm 0.5mm 0;
        }

        .meta-cell:nth-child(odd) {
            border-right: 1px solid #aaa;
            padding-right: 1.5mm;
        }

        .meta-cell:nth-child(even) {
            padding-left: 1.5mm;
        }

        .meta-label {
            display: block;
            font-size: 5pt;
            margin-bottom: .3mm;
        }

        .meta-value {
            display: block;
            font-size: 6.2pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .section {
            flex: 0 0 auto;
            padding: 0.6mm 0;
            border-bottom: 1px solid #111;
            min-width: 0;
        }

        .section-title {
            font-size: 5.4pt;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .4mm;
        }

        .address-name {
            font-size: 6.2pt;
            font-weight: 700;
            overflow-wrap: anywhere;
            margin-bottom: 0.6mm;
        }

        .address-phone {
            margin-top: 0;
            font-size: 5.8pt;
            overflow-wrap: anywhere;
            margin-bottom: 0.6mm;
        }

        .address-text {
            margin-top: .3mm;
            font-size: 5.4pt;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .items {
            flex: 1 1 auto;
            min-height: 8mm;
            max-height: 18mm;
            overflow: hidden;
            padding: 0.6mm 0;
            border-bottom: 1px solid #111;
            margin-left: -2mm;
            margin-right: -2mm;
            padding-left: 2mm;
            padding-right: 2mm;
        }

        .item-row {
            display: grid;
            grid-template-columns: 7mm 1fr;
            gap: 1mm;
            padding: .3mm 0;
            font-size: 5.4pt;
            line-height: 1.1;
        }

        .item-qty {
            font-weight: 700;
            white-space: nowrap;
        }

        .item-name {
            overflow-wrap: anywhere;
        }

        .empty-items {
            font-size: 5.4pt;
        }

        .reference-barcode {
            margin-top: 0.5mm;
            width: 100%;
            text-align: center;
            overflow: hidden;
        }

        .reference-barcode svg {
            display: block;
            width: auto;
            max-width: 100%;
            height: 5.5mm;
            margin: 0 auto;
        }

        .reference {
            flex: 0 0 auto;
            padding: 0.5mm 0;
            border-bottom: 1px solid #111;
            margin-left: -2mm;
            margin-right: -2mm;
            padding-left: 2mm;
            padding-right: 2mm;
        }

        .reference-number {
            margin-top: .3mm;
            font-size: 6.2pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .footer {
            flex: 0 0 auto;
            padding-top: 0.5mm;
            text-align: center;
            font-size: 5pt;
            line-height: 1.1;
        }

        .footer strong {
            display: block;
            font-size: 5.4pt;
        }

        @media print {
            html,
            body {
                width: 80mm;
                height: 100mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .label-wrapper {
                width: 80mm;
                height: 100mm;
                margin: 0 auto;
                padding: 2mm 2mm;
                background: #fff;
            }

            .label {
                width: 100%;
                height: 100%;
                padding: 1.5mm 2mm;
                background: #fff;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                font-size: 5.2pt;
                line-height: 1.05;
                border: 1.5px solid #111;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <span>Label internal · 80 × 100 mm</span>
        <button type="button" onclick="window.print()">Cetak Label</button>
    </div>

    <div class="label-wrapper">
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
                @if($barcodeSvg)
                    <div class="tracking-barcode" aria-label="Barcode nomor resi">
                        {!! $barcodeSvg !!}
                    </div>
                @endif
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

            <section class="meta">
                <div class="meta-cell">
                    <div class="section-title">Alamat Penerima</div>
                    <div class="address-name">{{ $recipientName }}</div>
                    <div class="address-phone">{{ $recipientPhone }}</div>
                    <div class="address-text">
                        {{ $recipientAddress }}@if($recipientLocality !== ''), {{ $recipientLocality }}@endif
                    </div>
                </div>
                <div class="meta-cell">
                    <div class="section-title">Alamat Pengirim</div>
                    <div class="address-name">{{ $originName ?: 'Eazywear' }}</div>
                    <div class="address-phone">{{ $originPhone ?: '—' }}</div>
                    <div class="address-text">{{ $originAddressLine ?: 'Alamat pengirim belum dikonfigurasi' }}</div>
                </div>
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
                @if($referenceBarcodeSvg)
                    <div class="reference-barcode">
                        {!! $referenceBarcodeSvg !!}
                    </div>
                @endif
            </section>

            <footer class="footer">
                <strong>Pengiriman melalui platform Biteship</strong>
                biteship.com · Label internal Eazywear
            </footer>
        </main>
    </div>
</body>
</html>
