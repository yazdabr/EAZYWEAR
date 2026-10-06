<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Eazywear</title>
</head>

<body style="margin:0; padding:0; background-color:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#333333;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f5; padding:30px 15px;">
    <tr>
        <td align="center">

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px; background:#ffffff; border-radius:12px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="padding:28px 30px; border-bottom:1px solid #eeeeee;">
                        <div style="font-size:24px; font-weight:bold; color:#AE7C18;">
                            Eazywear
                        </div>
                        <div style="margin-top:6px; font-size:13px; color:#888888;">
                            Informasi Pesanan
                        </div>
                    </td>
                </tr>

                <!-- Greeting -->
                <tr>
                    <td style="padding:30px;">
                        <div style="font-size:18px; font-weight:bold; color:#222222; margin-bottom:12px;">
                            Halo {{ $transaction->shipping_name }},
                        </div>

                        <div style="font-size:14px; line-height:1.7; color:#555555;">
                            Pesanan Anda berhasil dibuat. Silakan lakukan pembayaran sebelum batas waktu yang ditentukan.
                        </div>

                        @if($transaction->payment_method === 'VA')
                            <div style="margin-top:18px; padding:14px 16px; background:#fff8e8; border:1px solid #f0d99a; border-radius:8px;">
                                <div style="font-size:13px; font-weight:bold; color:#7a5a12;">
                                    Cara Pembayaran
                                </div>
                                <div style="margin-top:6px; font-size:13px; line-height:1.7; color:#666666;">
                                    Gunakan nomor Virtual Account di atas untuk menyelesaikan pembayaran sebelum batas waktu pembayaran.
                                </div>
                            </div>
                        @elseif($transaction->payment_method === 'QRIS')
                            <div style="margin-top:18px; padding:14px 16px; background:#fff8e8; border:1px solid #f0d99a; border-radius:8px;">
                                <div style="font-size:13px; font-weight:bold; color:#7a5a12;">
                                    Cara Pembayaran QRIS
                                </div>
                                <div style="margin-top:6px; font-size:13px; line-height:1.7; color:#666666;">
                                    Gunakan QRIS pada halaman pembayaran untuk menyelesaikan transaksi.
                                    Pastikan nominal pembayaran sesuai dengan total invoice ini dan lakukan pembayaran sebelum batas waktu.
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>

                <!-- Detail Pesanan -->
                <tr>
                    <td style="padding:0 30px 24px 30px;">
                        <div style="font-size:16px; font-weight:bold; color:#222222; margin-bottom:12px;">
                            Detail Pesanan
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fafafa; border-radius:8px;">
                            <tr>
                                <td style="padding:14px 16px; color:#777777; font-size:13px; width:40%;">
                                    Invoice
                                </td>
                                <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222;">
                                    {{ $transaction->invoice_number }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                    Total Pembayaran
                                </td>
                                <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222;">
                                    Rp {{ number_format($transaction->total,0,',','.') }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                    Metode Pembayaran
                                </td>
                                <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222;">
                                    {{ $transaction->payment_method === 'VA' ? 'Virtual Account' : $transaction->payment_method }}
                                </td>
                            </tr>

                            @if($transaction->payment_method === 'VA')
                                <tr>
                                    <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                        Bank
                                    </td>
                                    <td style="padding:14px 16px; font-size:14px; color:#222222;">
                                        {{ $transaction->va_bank }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                        Nomor Virtual Account
                                    </td>
                                    <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222;">
                                        {{ $transaction->va_number }}
                                    </td>
                                </tr>

                                @if($transaction->va_expired_at)
                                    <tr>
                                        <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                            Batas Pembayaran
                                        </td>
                                        <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#AE7C18;">
                                            {{ $transaction->va_expired_at->copy()->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA
                                        </td>
                                    </tr>
                                @endif

                            @elseif($transaction->payment_method === 'QRIS')
                                @if($transaction->qris_reference_no)
                                    <tr>
                                        <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                            Referensi QRIS
                                        </td>
                                        <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222; word-break:break-all;">
                                            {{ $transaction->qris_reference_no }}
                                        </td>
                                    </tr>
                                @endif

                                @if($transaction->qris_expired_at)
                                    <tr>
                                        <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                            Batas Pembayaran
                                        </td>
                                        <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#AE7C18;">
                                            {{ $transaction->qris_expired_at->copy()->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        </table>
                    </td>
                </tr>

                <!-- Produk -->
                <tr>
                    <td style="padding:0 30px 24px 30px;">
                        <div style="font-size:16px; font-weight:bold; color:#222222; margin-bottom:12px;">
                            Produk
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            @foreach ($transaction->items as $item)
                                <tr>
                                    <td style="padding:14px 0; border-bottom:1px solid #eeeeee;">
                                        <div style="font-size:14px; font-weight:bold; color:#333333;">
                                            {{ $item->productVariant?->product?->name }}
                                        </div>
                                        <div style="font-size:13px; color:#888888; margin-top:5px;">
                                            Qty: {{ $item->qty }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:10px 30px 30px 30px;">
                        <div style="font-size:14px; line-height:1.7; color:#555555;">
                            Terima kasih telah berbelanja di Eazywear.
                        </div>

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