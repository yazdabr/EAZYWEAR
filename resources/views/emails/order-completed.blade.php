<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Selesai</title>
</head>
<body>
    <h2>Pesanan Selesai</h2>

    <p>Halo {{ $transaction->shipping_name }},</p>

    <p>
        Pesanan Anda dengan nomor invoice
        <strong>{{ $transaction->invoice_number }}</strong>
        telah selesai.
    </p>

    <p>
        Terima kasih telah berbelanja di Eazywear.
    </p>
</body>
</html>