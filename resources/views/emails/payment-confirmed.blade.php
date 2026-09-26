<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil</title>
</head>

<body style="font-family: Arial, sans-serif; color:#333;">

<h2 style="color:#AE7C18;">
    EazyWear
</h2>

<p>
Halo {{ $transaction->shipping_name }},
</p>

<p>
Pembayaran untuk pesanan Anda telah berhasil dikonfirmasi.
</p>

<table>
    <tr>
        <td>Invoice</td>
        <td>
            <strong>{{ $transaction->invoice_number }}</strong>
        </td>
    </tr>

    <tr>
        <td>Total Pembayaran</td>
        <td>
            Rp {{ number_format($transaction->total,0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Status</td>
        <td>
            Pembayaran Berhasil
        </td>
    </tr>

    <tr>
        <td>Waktu Pembayaran</td>
        <td>
            {{ $transaction->paid_at?->format('d M Y H:i') }}
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
Pesanan Anda akan segera diproses oleh tim EazyWear.
</p>


<p>
Terima kasih telah berbelanja di EazyWear.
</p>

</body>
</html>