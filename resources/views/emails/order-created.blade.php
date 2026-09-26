<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pesanan EazyWear</title>
</head>

<body style="font-family: Arial, sans-serif; color:#333;">

<h2 style="color:#AE7C18;">
    EazyWear
</h2>

<p>
Halo {{ $transaction->shipping_name }},
</p>

<p>
Pesanan Anda berhasil dibuat.
Silakan lakukan pembayaran sebelum batas waktu yang ditentukan.
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
        <td>Total Pembayaran</td>
        <td>
            Rp {{ number_format($transaction->total,0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Virtual Account</td>
        <td>
            {{ $transaction->va_bank }}
            -
            {{ $transaction->va_number }}
        </td>
    </tr>

    <tr>
        <td>Batas Pembayaran</td>
        <td>
            {{ $transaction->va_expired_at?->format('d M Y H:i') }}
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
Terima kasih telah berbelanja di EazyWear.
</p>


</body>
</html>