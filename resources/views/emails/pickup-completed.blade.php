<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih - Eazywear</title>
</head>
<body style="margin:0;padding:30px 15px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:620px;background:#fff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 30px;border-bottom:1px solid #eee;">
                            <div style="font-size:24px;font-weight:bold;color:#AE7C18;">Eazywear</div>
                            <div style="margin-top:6px;font-size:13px;color:#888;">Terima Kasih atas Pesanan Anda</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            <div style="font-size:18px;font-weight:bold;margin-bottom:12px;">
                                Halo {{ $transaction->shipping_name }},
                            </div>
                            <div style="font-size:14px;line-height:1.8;color:#555;">
                                Pesanan Anda telah berhasil diambil. Terima kasih telah mempercayakan kebutuhan
                                custom jersey dan sportswear Anda kepada Eazywear.
                                Semoga produk yang Anda terima sesuai dengan harapan.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background:#faf7ef;border:1px solid #ead9ae;border-radius:8px;">
                                <tr>
                                    <td style="padding:18px;">
                                        <div style="font-size:13px;color:#888;margin-bottom:6px;">Status Pesanan</div>
                                        <div style="font-size:16px;font-weight:bold;color:#AE7C18;">
                                            Pesanan Telah Diambil
                                        </div>
                                        <div style="margin-top:8px;font-size:13px;line-height:1.6;color:#666;">
                                            Pesanan dengan invoice
                                            <strong style="color:#333;">{{ $transaction->invoice_number }}</strong>
                                            telah diserahkan kepada Anda.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 24px;">
                            <div style="font-size:16px;font-weight:bold;margin-bottom:12px;">Detail Pesanan</div>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background:#fafafa;border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;color:#777;width:40%;">Invoice</td>
                                    <td style="padding:14px 16px;font-size:14px;font-weight:bold;">
                                        {{ $transaction->invoice_number }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;color:#777;">Total Pembayaran</td>
                                    <td style="padding:14px 16px;font-size:14px;font-weight:bold;">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;color:#777;">Metode Pengiriman</td>
                                    <td style="padding:14px 16px;font-size:14px;">
                                        {{ $transaction->shipping_method }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 30px;">
                            <div style="font-size:14px;line-height:1.8;color:#555;">
                                Terima kasih telah menjadi bagian dari Eazywear.
                                Kami menantikan pesanan Anda berikutnya!
                            </div>
                            <div style="margin-top:20px;font-size:12px;color:#999;">
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