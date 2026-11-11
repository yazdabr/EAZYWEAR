<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'STADIUM-LEGACY-20261108';
$date = '2026-11-08 12:00:00';

$rows = [
    // STADIUM HOME - reguler
    ['Stadium', 'Home', 'M', 3, false, false, false, 349999],
    ['Stadium', 'Home', 'L', 3, false, false, false, 349999],
    ['Stadium', 'Home', 'XL', 5, false, false, false, 349999],
    ['Stadium', 'Home', 'XL', 1, true, false, false, 449999],
    ['Stadium', 'Home', 'XXL', 2, false, false, false, 349999],
    ['Stadium', 'Home', '3XL', 1, false, false, false, 369999],
    ['Stadium', 'Home', '5XL', 1, false, false, false, 379999],

    // STADIUM HOME - NNS + PATCH
    // Tidak ada penjualan pada 8 November.

    // STADIUM AWAY - reguler
    ['Stadium', 'Away', 'S', 3, false, false, false, 349999],
    ['Stadium', 'Away', 'M', 4, false, false, false, 349999],
    ['Stadium', 'Away', 'M', 2, true, false, false, 449999],
    ['Stadium', 'Away', 'L', 9, false, false, false, 349999],
    ['Stadium', 'Away', 'XL', 5, false, false, false, 349999],
    ['Stadium', 'Away', 'XL', 2, true, false, false, 449999],
    ['Stadium', 'Away', 'XXL', 12, false, false, false, 349999],

    // STADIUM THIRD - reguler
    ['Stadium', 'Third', 'M', 1, false, false, false, 349999],
    ['Stadium', 'Third', 'L', 7, false, false, false, 349999],
    ['Stadium', 'Third', 'XL', 4, false, false, false, 349999],
    ['Stadium', 'Third', 'XXL', 1, false, false, false, 349999],
    ['Stadium', 'Third', '5XL', 1, false, false, false, 379999],
];

// Pastikan invoice belum pernah diimpor.
if (Transaction::where('invoice_number', $invoice)->exists()) {
    throw new RuntimeException("Invoice {$invoice} sudah ada.");
}

$items = [];
$subtotal = 0;

$totalStadium = 0;
$totalSupporter = 0;
$totalPolo = 0;

$totalNns = 0;
$totalPatch = 0;
$totalLongsleeve = 0;

foreach ($rows as [
    $version,
    $kit,
    $size,
    $qty,
    $longsleeve,
    $nns,
    $patch,
    $expectedPrice
]) {
    if ($version === 'Polo') {
        $productName = 'PS Barito Putera 2026/27 - Polo Shirt Grey';

        $variant = ProductVariant::query()
            ->with(['product', 'size'])
            ->whereHas('product', fn ($q) => $q->where('name', $productName))
            ->whereHas('size', fn ($q) => $q->where('name', $size))
            ->first();
    } else {
        $productName = "PS Barito Putera {$version} Version 2026/27 - {$kit}";

        $variant = ProductVariant::query()
            ->with(['product', 'size'])
            ->whereHas('product', fn ($q) => $q
                ->where('name', 'like', '%' . $version . ' Version 2026/27%')
                ->where('name', 'like', '%' . $kit))
            ->whereHas('size', fn ($q) => $q->where('name', $size))
            ->first();
    }

    if (!$variant) {
        throw new RuntimeException(
            "Varian tidak ditemukan: {$productName}, ukuran {$size}."
        );
    }

    $product = $variant->product;

    if ($nns && !$product->customization_enabled) {
        throw new RuntimeException("Customization tidak aktif: {$productName}");
    }

    if ($patch && !$product->patch_enabled) {
        throw new RuntimeException("Patch tidak aktif: {$productName}");
    }

    $basePrice = (int) $variant->price;

    $customizationFee = $nns
        ? (int) $product->customization_price
        : 0;

    // Tarif longsleeve historis per 8 November 2026.
    $longsleevePrice = $longsleeve ? 100000 : 0;

    $patchPrice = $patch
        ? (int) $product->patch_price
        : 0;

    $finalPrice = $basePrice
        + $customizationFee
        + $longsleevePrice
        + $patchPrice;

    // Validasi harga database terhadap data penjualan.
    if ($finalPrice !== $expectedPrice) {
        throw new RuntimeException(
            "Harga tidak cocok: {$productName} ukuran {$size}. " .
            "Database Rp{$finalPrice}, seharusnya Rp{$expectedPrice}."
        );
    }

    $lineSubtotal = $finalPrice * $qty;
    $subtotal += $lineSubtotal;

    $totalNns += $nns ? $qty : 0;
    $totalPatch += $patch ? $qty : 0;
    $totalLongsleeve += $longsleeve ? $qty : 0;

    if ($version === 'Stadium') {
        $totalStadium += $qty;
    } elseif ($version === 'Supporter') {
        $totalSupporter += $qty;
    } elseif ($version === 'Polo') {
        $totalPolo += $qty;
    }

    $items[] = [
        'product_variant_id' => $variant->id,
        'qty' => $qty,
        'price' => $finalPrice,
        'subtotal' => $lineSubtotal,
        'weight' => (int) ($variant->weight ?? 0),
        'custom_name' => $nns ? 'NNS' : null,
        'custom_number' => null,
        'is_longsleeve' => $longsleeve,
        'longsleeve_price' => $longsleevePrice,
        'is_patch' => $patch,
        'patch_price' => $patchPrice,
    ];
}

$totalQty = $totalStadium + $totalSupporter + $totalPolo;

// Validasi jumlah penjualan.
if (
    $totalStadium !== 67 ||
    $totalSupporter !== 0 ||
    $totalPolo !== 0 ||
    $totalQty !== 67
) {
    throw new RuntimeException(
        "Validasi jumlah gagal: Stadium={$totalStadium}, " .
        "Supporter={$totalSupporter}, Polo={$totalPolo}, Total={$totalQty}."
    );
}

// Validasi opsi tambahan.
if (
    $totalNns !== 0 ||
    $totalPatch !== 0 ||
    $totalLongsleeve !== 5
) {
    throw new RuntimeException(
        "Validasi opsi gagal: NNS={$totalNns}, " .
        "Patch={$totalPatch}, L/S={$totalLongsleeve}."
    );
}

// Validasi omzet.
if ($subtotal !== 24029933) {
    throw new RuntimeException(
        "Validasi omzet gagal: Rp" . number_format($subtotal, 0, ',', '.')
    );
}

echo "====================================\n";
echo "IMPORT TRANSAKSI STADIUM 8 NOVEMBER\n";
echo "====================================\n";
echo "INVOICE    : {$invoice}\n";
echo "TANGGAL    : {$date}\n";
echo "STADIUM    : {$totalStadium} pcs\n";
echo "SUPPORTER  : {$totalSupporter} pcs\n";
echo "POLO       : {$totalPolo} pcs\n";
echo "TOTAL      : {$totalQty} pcs\n";
echo "NNS        : {$totalNns} pcs\n";
echo "PATCH      : {$totalPatch} pcs\n";
echo "LONGSLEEVE : {$totalLongsleeve} pcs\n";
echo "OMZET      : Rp" . number_format($subtotal, 0, ',', '.') . "\n";
echo "STOK       : Tidak diubah\n";

if (!in_array('--import', $argv, true)) {
    echo "\nDRY RUN: belum ada transaksi yang disimpan.\n";
    echo "Periksa hasil sebelum menjalankan dengan --import.\n";
    exit(0);
}

DB::transaction(function () use ($invoice, $date, $subtotal, $items) {
    if (Transaction::where('invoice_number', $invoice)->lockForUpdate()->exists()) {
        throw new RuntimeException("Invoice {$invoice} sudah ada.");
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
        'source' => 'Stadium',
        'shipping_name' => 'Transaksi Historis 8 November 2026',
    ]);

    foreach ($items as $item) {
        $transaction->items()->create($item);
    }
});

echo "\nIMPORT BERHASIL.\n";
