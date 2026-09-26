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
Pembayaran untuk pesanan berikut telah melewati batas waktu.
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
<td>Status</td>
<td>
Pembayaran Kadaluarsa
</td>
</tr>


</table>


<p>
Silakan membuat pesanan baru apabila masih ingin membeli produk tersebut.
</p>


</body>
</html>