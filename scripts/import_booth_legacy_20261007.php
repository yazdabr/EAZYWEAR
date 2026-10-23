<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'BOOTH-LEGACY-20261007';
$date = '2026-10-07 12:00:00';

$rows = [
    ['Stadium', 'Home',  'M',   6, false],
    ['Stadium', 'Home',  'L',   5, false],
    ['Stadium', 'Home',  'XL',  5, false],
    ['Stadium', 'Home',  'XL',  1, true],
    ['Stadium', 'Home',  'XXL', 3, false],
    ['Stadium', 'Home',  '3XL', 1, false],
    ['Stadium', 'Away',  'XL',  1, false],
    ['Stadium', 'Third', 'L',   1, true],
    ['Supporter', 'Home', 'XS',  2, false],
    ['Supporter', 'Home', 'S',   1, false],
    ['Supporter', 'Home', 'L',   2, false],
    ['Supporter', 'Home', 'XL',  1, false],
    ['Supporter', 'Home', 'XXL', 1, false],
    ['Supporter', 'Home', '3XL', 2, false],
    ['Supporter', 'Away', 'S',   1, false],
    ['Supporter', 'Away', 'M',   7, false],
    ['Supporter', 'Away', 'L',   5, false],
    ['Supporter', 'Away', 'XL',  7, false],
    ['Supporter', 'Away', 'XXL', 1, false],
    ['Supporter', 'Third', 'S',  1, false],
    ['Supporter', 'Third', 'M',  5, false],
    ['Supporter', 'Third', 'L',  5, false],
    ['Supporter', 'Third', 'XL', 6, false],
    ['Supporter', 'Third', 'XXL', 2, false],
];

if (array_sum(array_column($rows, 3)) !== 72 || count($rows) !== 24) {
    throw new RuntimeException('Validasi jumlah gagal: seharusnya 24 baris dan 72 pcs.');
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

    $unitPrice = $basePrice + $longsleevePrice;
    $lineSubtotal = $unitPrice * $qty;
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
