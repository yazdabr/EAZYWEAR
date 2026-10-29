<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koreksi Jadwal Pengambilan Pre-Order</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;color:#222;">
<div style="max-width:600px;margin:0 auto;padding:24px 16px;">
    <div style="background:#111;padding:28px 24px;text-align:center;">
        <h1 style="margin:0;color:#C99322;font-size:28px;letter-spacing:3px;">EAZYWEAR</h1>
        <p style="margin:10px 0 0;color:#fff;font-size:13px;">Koreksi Jadwal Pengambilan Pre-Order</p>
    </div>

    <div style="background:#fff;padding:30px 24px;line-height:1.7;">
        <p>Halo {{ $customerName ?: 'Pelanggan Eazywear' }},</p>

        <p>
            Kami ingin mengoreksi informasi pada email sebelumnya mengenai
            jadwal pengiriman pesanan pre-order.
        </p>

        <p>
            Bagi pelanggan yang memilih metode <strong>Ambil di Tempat</strong>,
            pengambilan pesanan tersedia pada periode
            <strong>30 Oktober&ndash;3 November 2026</strong>, sesuai tanggal dan jam
            yang telah dipilih saat checkout.
        </p>

        <p>
            Silakan datang ke Kantor Eazywear sesuai jadwal pengambilan pada
            pesanan Anda. Anda tidak perlu menunggu pengiriman melalui kurir
            karena metode yang dipilih adalah pengambilan langsung.
        </p>

        @foreach ($orders as $order)
            <div style="margin:20px 0;padding:16px;background:#fff8e8;border-left:4px solid #C99322;">
                <p style="margin:0 0 8px;">
                    <strong>Invoice:</strong> {{ $order['invoice'] }}
                </p>

                @foreach ($order['schedules'] as $schedule)
                    <p style="margin:0 0 6px;">
                        <strong>Tanggal pengambilan:</strong> {{ $schedule['date'] }}
                    </p>
                    <p style="margin:0 0 8px;">
                        <strong>Waktu:</strong> {{ $schedule['time'] }}
                    </p>
                @endforeach
            </div>
        @endforeach

        <p>
            Mohon periksa kembali jadwal di atas sebelum datang. Jika
            membutuhkan konfirmasi lebih lanjut, silakan hubungi tim Eazywear.
        </p>

        <p>
            Mohon maaf atas kekeliruan informasi sebelumnya. Terima kasih
            atas pengertian dan kepercayaan Anda.
        </p>

        <p style="margin-bottom:0;">
            Salam,<br><strong>Tim Eazywear</strong>
        </p>
    </div>

    <div style="padding:18px;text-align:center;color:#777;font-size:12px;">
        &copy; {{ date('Y') }} Eazywear. All rights reserved.
    </div>
</div>
</body>
</html>
