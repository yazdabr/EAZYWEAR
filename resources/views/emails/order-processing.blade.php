<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Sedang Diproses - Eazywear</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 15px;background:#f5f5f5;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:620px;background:#fff;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="padding:28px 30px;border-bottom:1px solid #eee;">
                        <div style="font-size:24px;font-weight:bold;color:#AE7C18;">Eazywear</div>
                        <div style="margin-top:6px;font-size:13px;color:#888;">
                            Informasi Pesanan
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px;">
                        <div style="font-size:18px;font-weight:bold;margin-bottom:12px;">
                            Halo {{ $transaction->shipping_name ?: 'Pelanggan Eazywear' }},
                        </div>
                        <div style="font-size:14px;line-height:1.8;color:#555;">
                            Kabar baik! Pembayaran Anda telah dikonfirmasi dan pesanan
                            Anda sekarang sedang diproses oleh tim Eazywear.
                            Kami sedang menyiapkan pesanan agar siap untuk tahap
                            pengiriman atau pengambilan.
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="padding:16px;background:#f8f3e8;border-radius:8px;border-left:4px solid #AE7C18;">
                            <div style="font-size:13px;color:#777;">Status Pesanan</div>
                            <div style="margin-top:5px;font-size:16px;font-weight:bold;color:#AE7C18;">
                                Pesanan Sedang Diproses
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="font-size:16px;font-weight:bold;margin-bottom:12px;">
                            Detail Pesanan
                        </div>
                        <table width="100%" cellpadding="0" cellspacing="0"
                               style="background:#fafafa;border-radius:8px;">
                            <tr>
                                <td style="padding:14px 16px;color:#777;font-size:13px;">Nomor Invoice</td>
                                <td style="padding:14px 16px;font-size:14px;font-weight:bold;">
                                    {{ $transaction->invoice_number }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#777;font-size:13px;">Total</td>
                                <td style="padding:14px 16px;font-size:14px;font-weight:bold;">
                                    Rp {{ number_format((float) $transaction->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="font-size:16px;font-weight:bold;margin-bottom:12px;">Produk</div>
                        <table width="100%" cellpadding="0" cellspacing="0">
                            @foreach($transaction->items as $item)
                                <tr>
                                    <td style="padding:14px 0;border-bottom:1px solid #eee;">
                                        <div style="font-size:14px;font-weight:bold;">
                                            {{ $item->productVariant?->product?->name ?? 'Produk Eazywear' }}
                                        </div>
                                        <div style="margin-top:5px;font-size:13px;color:#888;">
                                            Jumlah: {{ $item->qty }}
                                            @if($item->custom_name)
                                                · Nama jersey: {{ $item->custom_name }}
                                            @endif
                                            @if($item->custom_number)
                                                · Nomor: {{ $item->custom_number }}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px 30px 30px;">
                        <div style="font-size:14px;line-height:1.8;color:#555;">
                            Mohon menunggu informasi berikutnya. Jika pesanan menggunakan
                            kurir, informasi pengiriman akan disampaikan setelah pesanan dikirim.
                        </div>
                        <div style="margin-top:20px;font-size:14px;color:#555;">
                            Terima kasih telah berbelanja di Eazywear!
                        </div>
                        <div style="margin-top:20px;font-size:12px;color:#999;">
                            Email ini dikirim otomatis oleh sistem Eazywear.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
