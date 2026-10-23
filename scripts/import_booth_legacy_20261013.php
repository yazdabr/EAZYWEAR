<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'BOOTH-LEGACY-20261013';
$date = '2026-10-13 12:00:00';

$rows = [
    ['Supporter', 'Home',  'M',  1, false],
    ['Supporter', 'Home',  'L',  2, false],
    ['Supporter', 'Third', 'M',  1, false],
    ['Supporter', 'Third', 'XL', 1, false],
];

if (array_sum(array_column($rows, 3)) !== 5) {
    throw new RuntimeException('Validasi jumlah gagal: seharusnya 5 pcs.');
}

if (Transaction::where('invoice_number', $invoice)->exists()) {
    throw new RuntimeException("Invoice {$invoice} sudah ada. Impor dibatalkan.");
}

$items = [];
$subtotal = 0;

foreach ($rows as [$version, $kit, $size, $qty, $longsleeve]) {
    $name = "PS Barito Putera {$version} Version 2026/27 – {$kit}";

    $variant = ProductVariant::query()
        ->with(['product', 'size'])
        ->whereHas('product', fn ($q) => $q->where('name', $name))
        ->whereHas('size', fn ($q) => $q->where('name', $size))
        ->first();

    if (!$variant) {
        throw new RuntimeException("Varian tidak ditemukan: {$name}, ukuran {$size}.");
    }

    $basePrice = (float) $variant->price;
    $longsleevePrice = $longsleeve && $version === 'Stadium'
        ? (int) ($variant->product->longsleeve_price ?? 0)
        : 0;

    $lineSubtotal = ($basePrice + $longsleevePrice) * $qty;
    $subtotal += $lineSubtotal;

    $items[] = [
        'product_variant_id' => $variant->id,
        'qty' => $qty,
        'price' => $basePrice,
        'subtotal' => $lineSubtotal,
        'weight' => (int) ($variant->weight ?? 0),
        'custom_name' => null,
        'custom_number' => null,
        'is_longsleeve' => $longsleeve,
        'longsleeve_price' => $longsleevePrice,
        'is_patch' => false,
        'patch_price' => 0,
    ];
}

echo "Invoice: {$invoice}\n";
echo "Tanggal: {$date}\n";
echo "Jumlah baris: " . count($items) . "\n";
echo "Jumlah pcs: " . array_sum(array_column($rows, 3)) . "\n";
echo "Subtotal hasil perhitungan: Rp" . number_format($subtotal, 0, ',', '.') . "\n";
echo "Stok akan diubah: TIDAK\n";
echo "Stock movement dibuat: TIDAK\n";

if (!in_array('--import', $argv, true)) {
    echo "\nDRY RUN: belum ada data yang disimpan.\n";
    exit(0);
}

DB::transaction(function () use ($invoice, $date, $items, $subtotal) {
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
        'source' => 'Booth',
        'shipping_name' => 'Transaksi Historis',
    ]);

    foreach ($items as $item) {
        $transaction->items()->create($item);
    }
});

echo "\nIMPORT BERHASIL: transaksi historis sudah disimpan.\n";
