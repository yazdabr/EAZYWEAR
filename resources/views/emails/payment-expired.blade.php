<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Kadaluarsa - Eazywear</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#333333;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding:30px 15px;">
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
                            Informasi Pembayaran
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
                            Pembayaran untuk pesanan berikut telah melewati batas waktu.
                        </div>
                    </td>
                </tr>

                <!-- Status -->
                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="padding:16px; background:#fafafa; border-radius:8px; border-left:4px solid #999999;">
                            <div style="font-size:13px; color:#777777;">
                                Status
                            </div>

                            <div style="margin-top:5px; font-size:16px; font-weight:bold; color:#555555;">
                                Pembayaran Kadaluarsa
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Detail -->
                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="font-size:16px; font-weight:bold; margin-bottom:12px;">
                            Detail Pesanan
                        </div>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fafafa; border-radius:8px;">
                            <tr>
                                <td style="padding:14px 16px; color:#777777; font-size:13px; width:40%;">
                                    Invoice
                                </td>

                                <td style="padding:14px 16px; font-size:14px; font-weight:bold;">
                                    {{ $transaction->invoice_number }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Message -->
                <tr>
                    <td style="padding:0 30px 24px;">
                        <div style="padding:16px; background:#fafafa; border-radius:8px;">
                            <div style="font-size:14px; line-height:1.7; color:#555555;">
                                Silakan membuat pesanan baru apabila masih ingin membeli produk tersebut.
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:10px 30px 30px;">
                        <div style="font-size:14px; color:#555555;">
                            Terima kasih telah menggunakan Eazywear.
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