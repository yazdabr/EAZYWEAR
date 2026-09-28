<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pesanan Telah Dikirim</title>
</head>

<body style="font-family: Arial, sans-serif; color:#333;">

<h2 style="color:#AE7C18;">
    EazyWear
</h2>

<p>
Halo {{ $transaction->shipping_name }},
</p>

<p>
Pesanan Anda telah dikirim oleh tim EazyWear.
Berikut informasi pengiriman pesanan Anda.
</p>


<h3>Detail Pesanan</h3>

<table>
    <tr>
        <td>Invoice</td>
        <td>
            <strong>{{ $transaction->invoice_number }}</strong>
        </td>
    </tr>

    <tr>
        <td>Status</td>
        <td>
            Pesanan Dikirim
        </td>
    </tr>

    <tr>
        <td>Kurir</td>
        <td>
            {{ $transaction->courier ?? '-' }}
        </td>
    </tr>

    <tr>
        <td>Nomor Resi</td>
        <td>
            <strong>{{ $transaction->tracking_number ?? '-' }}</strong>
        </td>
    </tr>
</table>


<h3>Produk</h3>

<ul>
@foreach($transaction->items as $item)

<li>
    {{ $item->productVariant?->product?->name }}
    <br>
    Qty: {{ $item->qty }}
</li>

@endforeach
</ul>


<p>
Silakan gunakan nomor resi di atas untuk melacak pengiriman pesanan Anda melalui layanan kurir terkait.
</p>


<p>
Terima kasih telah berbelanja di EazyWear.
</p>

</body>
</html>