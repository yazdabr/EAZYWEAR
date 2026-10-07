<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Selesai - Eazywear</title>
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
                            @if ($transaction->shipping_method === 'Ambil di Tempat')
                                Pesanan Anda telah selesai dan siap untuk diambil.
                                Silakan ambil pesanan Anda sesuai dengan jadwal pengambilan
                                yang telah Anda tentukan saat melakukan pemesanan.
                            @else
                                Pesanan Anda telah selesai. Terima kasih telah berbelanja dan mempercayakan kebutuhan
                                custom jersey dan sportswear Anda kepada Eazywear.
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Status -->
                <tr>
                    <td style="padding:0 30px 24px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background:#faf7ef; border:1px solid #ead9ae; border-radius:8px;">
                            <tr>
                                <td style="padding:16px 18px;">
                                    <div style="font-size:13px; color:#888888; margin-bottom:5px;">
                                        Status Pesanan
                                    </div>

                                    <div style="font-size:16px; font-weight:bold; color:#AE7C18;">
                                        @if ($transaction->shipping_method === 'Ambil di Tempat')
                                            Pesanan Siap Diambil
                                        @else
                                            Pesanan Selesai
                                        @endif
                                    </div>

                                    <div style="margin-top:6px; font-size:13px; line-height:1.6; color:#666666;">
                                        @if ($transaction->shipping_method === 'Ambil di Tempat')
                                            Pesanan dengan nomor invoice
                                            <strong style="color:#333333;">
                                                {{ $transaction->invoice_number }}
                                            </strong>
                                            telah selesai diproses dan siap untuk diambil sesuai dengan jadwal yang telah Anda pilih.
                                        @else
                                            Pesanan dengan nomor invoice
                                            <strong style="color:#333333;">
                                                {{ $transaction->invoice_number }}
                                            </strong>
                                            telah selesai diproses.
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Detail Pesanan -->
                <tr>
                    <td style="padding:0 30px 24px 30px;">
                        <div style="font-size:16px; font-weight:bold; color:#222222; margin-bottom:12px;">
                            Detail Pesanan
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background:#fafafa; border-radius:8px;">

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
                                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                    Metode Pengiriman
                                </td>

                                <td style="padding:14px 16px; font-size:14px; color:#222222;">
                                    {{ $transaction->shipping_method }}
                                </td>
                            </tr>

                            @if ($transaction->shipping_method === 'Ambil di Tempat' &&
                                $transaction->pickup_date &&
                                $transaction->pickup_time_start &&
                                $transaction->pickup_time_end)
                                <tr>
                                    <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                        Jadwal Pengambilan
                                    </td>

                                    <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222;">
                                        {{ \Carbon\Carbon::parse($transaction->pickup_date)->locale('id')->translatedFormat('l, d F Y') }},
                                        {{ \Carbon\Carbon::parse($transaction->pickup_time_start)->format('H:i') }}–{{ \Carbon\Carbon::parse($transaction->pickup_time_end)->format('H:i') }}
                                        WITA
                                    </td>
                                </tr>
                            @endif

                            @if ($transaction->shipping_method === 'Kurir' && $transaction->courier)
                                <tr>
                                    <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                        Kurir
                                    </td>

                                    <td style="padding:14px 16px; font-size:14px; color:#222222;">
                                        {{ $transaction->courier }}
                                    </td>
                                </tr>
                            @endif

                            @if ($transaction->shipping_method === 'Kurir' && $transaction->tracking_number)
                                <tr>
                                    <td style="padding:14px 16px; color:#777777; font-size:13px;">
                                        Nomor Resi
                                    </td>

                                    <td style="padding:14px 16px; font-size:14px; font-weight:bold; color:#222222;">
                                        {{ $transaction->tracking_number }}
                                    </td>
                                </tr>
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

                <!-- Closing -->
                <tr>
                    <td style="padding:0 30px 10px 30px;">
                        <div style="font-size:14px; line-height:1.7; color:#555555;">
                            @if ($transaction->shipping_method === 'Ambil di Tempat')
                                Terima kasih telah berbelanja di Eazywear.
                                Jangan lupa untuk mengambil pesanan Anda sesuai dengan jadwal yang telah ditentukan.
                                Kami menunggu kedatangan Anda.
                            @else
                                Terima kasih telah berbelanja di Eazywear.
                                Kami berharap produk Anda sesuai dengan kebutuhan dan harapan.
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:10px 30px 30px 30px;">
                        <div style="font-size:12px; color:#999999;">
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
