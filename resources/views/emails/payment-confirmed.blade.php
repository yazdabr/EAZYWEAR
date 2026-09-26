<!DOCTYPE html>
<html>
<body style="font-family:Arial;color:#333">

<h2 style="color:#AE7C18">
EazyWear
</h2>


<p>
Halo {{ $transaction->shipping_name }},
</p>


<p>
Pembayaran Anda telah berhasil dikonfirmasi.
</p>


<table>

<tr>
<td>Invoice</td>
<td>
<strong>
{{ $transaction->invoice_number }}
</strong>
</td>
</tr>


<tr>
<td>Total</td>
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


</table>


<p>
Pesanan Anda akan segera diproses.
</p>


<p>
Terima kasih telah memilih EazyWear.
</p>


</body>
</html>