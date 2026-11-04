<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Telah Dikirim - Eazywear</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#333333;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding:30px 15px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px; background:#ffffff; border-radius:12px; overflow:hidden;">

                <tr>
                    <td style="padding:28px 30px; border-bottom:1px solid #eeeeee;">
                        <div style="font-size:24px; font-weight:bold; color:#AE7C18;">Eazywear</div>
                        <div style="margin-top:6px; font-size:13px; color:#888888;">Informasi Pengiriman</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px;">
                        <div style="font-size:18px; font-weight:bold; color:#222222; margin-bottom:12px;">
                            Halo {{ $transaction->shipping_name }},
                        </div>
                        <div style="font-size:14px; line-height:1.7; color:#555555;">
                            Pesanan Anda telah dikirim oleh tim Eazywear. Berikut informasi pengiriman pesanan Anda.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="padding:16px; background:#f8f3e8; border-radius:8px; border-left:4px solid #AE7C18;">
                            <div style="font-size:13px; color:#777777;">Status</div>
                            <div style="margin-top:5px; font-size:16px; font-weight:bold; color:#AE7C18;">
                                Pesanan Dikirim
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="font-size:16px; font-weight:bold; margin-bottom:12px;">Informasi Pengiriman</div>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fafafa; border-radius:8px;">
                            <tr>
                                <td style="padding:14px 16px; color:#777777; font-size:13px; width:40%;">Invoice</td>
                                <td style="padding:14px 16px; font-size:14px; font-weight:bold;">
                                    {{ $transaction->invoice_number }}
                                </td>
                            </tr>
                        </table>
                        @forelse($transaction->shipments as $shipment)
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:12px; background:#fafafa; border-radius:8px;">
                                <tr>
                                    <td colspan="2" style="padding:14px 16px 6px; font-size:14px; font-weight:bold;">
                                        {{ $shipment->shipment_type === 'combined' ? 'Pengiriman Gabungan' : 'Pengiriman' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px; color:#777777; font-size:13px; width:40%;">Kurir</td>
                                    <td style="padding:8px 16px; font-size:14px;">
                                        {{ $shipment->courier ?: $transaction->courier ?: $shipment->shipping_method ?: $transaction->shipping_method ?: '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px 14px; color:#777777; font-size:13px;">Nomor Resi</td>
                                    <td style="padding:8px 16px 14px; font-size:14px; font-weight:bold; color:#AE7C18;">
                                        {{ $shipment->tracking_number ?: $shipment->biteship_waybill_id ?: $transaction->tracking_number ?: $transaction->biteship_waybill_id ?: '-' }}
                                    </td>
                                </tr>
                            </table>
                        @empty
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:12px; background:#fafafa; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px; color:#777777; font-size:13px; width:40%;">Kurir</td>
                                    <td style="padding:14px 16px; font-size:14px;">
                                        {{ $transaction->courier ?: $transaction->shipping_method ?: '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px 14px; color:#777777; font-size:13px;">Nomor Resi</td>
                                    <td style="padding:8px 16px 14px; font-size:14px; font-weight:bold; color:#AE7C18;">
                                        {{ $transaction->tracking_number ?: $transaction->biteship_waybill_id ?: '-' }}
                                    </td>
                                </tr>
                            </table>
                        @endforelse
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="font-size:16px; font-weight:bold; margin-bottom:12px;">Detail Produk</div>

                        @foreach($transaction->items as $item)
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px; background:#fafafa; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="font-size:14px; font-weight:bold; color:#222222;">
                                            {{ $item->productVariant?->product?->name ?? $item->custom_name ?? 'Produk' }}
                                        </div>

                                        <div style="margin-top:5px; font-size:13px; color:#777777;">
                                            Ukuran: {{ $item->productVariant?->size?->name ?? '-' }}
                                            &nbsp; | &nbsp; Qty: {{ $item->qty }} pcs
                                        </div>

                                        @if($item->custom_name)
                                            <div style="margin-top:9px; font-size:13px;">
                                                <span style="color:#777777;">NAMA:</span>
                                                <strong style="color:#AE7C18;">{{ mb_strtoupper($item->custom_name, 'UTF-8') }}</strong>
                                            </div>
                                        @endif

                                        @if($item->custom_number)
                                            <div style="margin-top:5px; font-size:13px;">
                                                <span style="color:#777777;">NOMOR:</span>
                                                <strong style="color:#AE7C18;">{{ $item->custom_number }}</strong>
                                            </div>
                                        @endif

                                        @if($item->is_patch)
                                            <div style="margin-top:5px; font-size:13px;">
                                                <strong style="color:#AE7C18;">PATCH</strong>
                                            </div>
                                        @endif

                                        @if($item->is_longsleeve)
                                            <div style="margin-top:5px; font-size:13px;">
                                                <strong style="color:#AE7C18;">LONGSLEEVE</strong>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="padding:16px; background:#fafafa; border-radius:8px;">
                            <div style="font-size:14px; font-weight:bold; margin-bottom:7px;">Lacak Pengiriman</div>
                            <div style="font-size:13px; line-height:1.7; color:#666666;">
                                Silakan gunakan nomor resi di atas untuk melacak pengiriman pesanan Anda melalui layanan kurir terkait.
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:10px 30px 30px;">
                        <div style="font-size:14px; color:#555555;">Terima kasih telah berbelanja di Eazywear.</div>
                        <div style="margin-top:20px; font-size:12px; color:#999999;">
                            Email ini dikirim secara otomatis oleh sistem Eazywear.
                        </div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
