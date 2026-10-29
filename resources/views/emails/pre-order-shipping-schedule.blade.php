
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Jadwal Pengiriman Pre-order</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family:Arial,Helvetica,sans-serif; color:#222222;">
    <div style="max-width:600px; margin:0 auto; padding:24px 16px;">
        <div style="background-color:#111111; padding:28px 24px; text-align:center;">
            <h1 style="margin:0; color:#C99322; font-size:28px; letter-spacing:3px;">
                EAZYWEAR
            </h1>
            <p style="margin:10px 0 0; color:#ffffff; font-size:13px;">
                Informasi Jadwal Pengiriman Pre-order
            </p>
        </div>

        <div style="background-color:#ffffff; padding:30px 24px; line-height:1.7;">
            <p style="margin-top:0;">
                Halo {{ $transaction->shipping_name ?: 'Pelanggan Eazywear' }},
            </p>

            <p>
                Terima kasih telah melakukan pemesanan di Eazywear.
            </p>

            <p>
                Kami informasikan bahwa proses pengiriman pesanan pre-order
                akan mulai dilakukan pada:
            </p>

            <div style="margin:24px 0; padding:20px; background-color:#fff8e8; border-left:4px solid #C99322; text-align:center;">
                <p style="margin:0 0 8px; color:#555555; font-size:13px;">
                    MULAI PENGIRIMAN
                </p>
                <p style="margin:0; color:#111111; font-size:24px; font-weight:bold;">
                    3 November 2026
                </p>
            </div>

            <p>
                Pengiriman akan dilakukan secara bertahap. Tanggal tersebut
                merupakan awal proses pengiriman, bukan jaminan bahwa pesanan
                akan tiba pada tanggal yang sama.
            </p>

            <p>
                Terima kasih atas kesabaran dan kepercayaan Anda kepada Eazywear.
            </p>

            <p style="margin-bottom:0;">
                Salam hangat,<br>
                <strong>Tim Eazywear</strong>
            </p>
        </div>

        <div style="padding:18px; text-align:center; color:#777777; font-size:12px;">
            © {{ date('Y') }} Eazywear. All rights reserved.
        </div>
    </div>
</body>
</html>
