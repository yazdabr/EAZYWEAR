<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'STADIUM-LEGACY-20260912';
$date = '2026-09-12 12:00:00';
$source = 'Stadium';

$rows = [
    ['Stadium', 'Home', 'S', 4],
    ['Stadium', 'Home', 'M', 9],
    ['Stadium', 'Home', 'L', 20],
    ['Stadium', 'Home', 'XL', 18],
    ['Stadium', 'Home', 'XXL', 3],

    ['Supporter', 'Home', 'XS', 15],
    ['Supporter', 'Home', 'S', 60],
    ['Supporter', 'Home', 'M', 60],
    ['Supporter', 'Home', 'L', 60],
    ['Supporter', 'Home', 'XL', 60],
    ['Supporter', 'Home', 'XXL', 50],
    ['Supporter', 'Home', '3XL', 50],
    ['Supporter', 'Home', '4XL', 30],
    ['Supporter', 'Home', '5XL', 15],
];

$totalQty = array_sum(array_column($rows, 3));

if (count($rows) !== 14 || $totalQty !== 454) {
    throw new RuntimeException('Validasi gagal: harus 14 baris dan 454 pcs.');
}

if (Transaction::where('invoice_number', $invoice)->exists()) {
    throw new RuntimeException("Invoice {$invoice} sudah ada. Impor dibatalkan.");
}

$items = [];
$subtotal = 0;

foreach ($rows as [$version, $kit, $size, $qty]) {
    $name = "PS Barito Putera {$version} Version 2026/27 – {$kit}";

    $variant = ProductVariant::query()
        ->with(['product', 'size'])
        ->whereHas('product', fn ($q) => $q->where('name', $name))
        ->whereHas('size', fn ($q) => $q->where('name', $size))
        ->first();

    if (!$variant) {
        throw new RuntimeException("Varian tidak ditemukan: {$name}, ukuran {$size}.");
    }

    $unitPrice = (float) $variant->price;
    $lineSubtotal = $unitPrice * $qty;
    $subtotal += $lineSubtotal;

    $items[] = [
        'product_variant_id' => $variant->id,
        'qty' => $qty,
        'price' => $unitPrice,
        'subtotal' => $lineSubtotal,
        'weight' => (int) ($variant->weight ?? 0),
        'custom_name' => null,
        'custom_number' => null,
        'is_longsleeve' => false,
        'longsleeve_price' => 0,
        'is_patch' => false,
        'patch_price' => 0,
    ];
}

echo "Invoice        : {$invoice}\n";
echo "Tanggal        : {$date}\n";
echo "Source         : {$source}\n";
echo "Jumlah baris   : " . count($items) . "\n";
echo "Jumlah pcs     : {$totalQty}\n";
echo "Subtotal       : Rp" . number_format($subtotal, 0, ',', '.') . "\n";
echo "Stok diubah    : TIDAK\n";
echo "Stock movement : TIDAK\n";

if (!in_array('--import', $argv, true)) {
    echo "\nDRY RUN: belum ada data yang disimpan.\n";
    exit(0);
}

DB::transaction(function () use ($invoice, $date, $source, $items, $subtotal) {
    if (Transaction::where('invoice_number', $invoice)->lockForUpdate()->exists()) {
        throw new RuntimeException("Invoice {$invoice} sudah ada. Impor dibatalkan.");
    }

    $transaction = Transaction::create([
        'customer_id' => null,
        'invoice_number' => $invoice,
        'transaction_date' => $date,
        'payment_method' => 'CASH',
        'subtotal' => $subtotal,
        'discount' => 0,
        'shipping' => 0,
        'total' => $subtotal,
        'status' => 'PAID',
        'paid_at' => $date,
        'source' => $source,
        'shipping_name' => 'Transaksi Historis',
    ]);

    foreach ($items as $item) {
        $transaction->items()->create($item);
    }
});

echo "\nIMPORT BERHASIL: transaksi Stadion sudah disimpan.\n";
