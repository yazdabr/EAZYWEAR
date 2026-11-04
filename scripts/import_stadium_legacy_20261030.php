<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'STADIUM-LEGACY-20261030';
$date = '2026-10-30 12:00:00';

$rows = [
    // STADIUM HOME - reguler
    ['Stadium', 'Home', 'S', 1, false, false, false, 349999],
    ['Stadium', 'Home', 'M', 1, false, false, false, 349999],
    ['Stadium', 'Home', 'L', 7, false, false, false, 349999],
    ['Stadium', 'Home', 'XL', 5, false, false, false, 349999],
    ['Stadium', 'Home', 'XXL', 2, false, false, false, 349999],
    ['Stadium', 'Home', '3XL', 1, false, false, false, 369999],
    ['Stadium', 'Home', '4XL', 1, false, false, false, 374999],

    // STADIUM HOME - NNS + PATCH
    ['Stadium', 'Home', 'L', 1, false, true, true, 524999],

    // STADIUM AWAY - reguler
    ['Stadium', 'Away', 'M', 2, false, false, false, 349999],
    ['Stadium', 'Away', 'L', 3, false, false, false, 349999],
    ['Stadium', 'Away', 'XL', 4, false, false, false, 349999],
    ['Stadium', 'Away', 'XXL', 3, false, false, false, 349999],
    ['Stadium', 'Away', '3XL', 1, false, false, false, 369999],

    // STADIUM THIRD - reguler
    ['Stadium', 'Third', 'M', 4, false, false, false, 349999],
    ['Stadium', 'Third', 'M', 1, true, false, false, 449999],
    ['Stadium', 'Third', 'L', 7, false, false, false, 349999],
    ['Stadium', 'Third', 'XL', 8, false, false, false, 349999],
    ['Stadium', 'Third', 'XL', 3, true, false, false, 449999],
    ['Stadium', 'Third', 'XXL', 11, false, false, false, 349999],
    ['Stadium', 'Third', 'XXL', 2, true, false, false, 449999],
    ['Stadium', 'Third', '3XL', 2, false, false, false, 369999],
    ['Stadium', 'Third', '4XL', 2, false, false, false, 374999],

    // SUPPORTER HOME
    ['Supporter', 'Home', '3XL', 1, false, false, false, 194999],

    // SUPPORTER AWAY
    ['Supporter', 'Away', 'XS', 2, false, false, false, 174999],

    // SUPPORTER THIRD
    ['Supporter', 'Third', 'S', 1, false, false, false, 174999],
    ['Supporter', 'Third', 'M', 1, false, false, false, 174999],
    ['Supporter', 'Third', 'L', 1, false, false, false, 174999],

    // POLO SHIRT GREY
    ['Polo', 'Grey', 'M', 1, false, false, false, 299999],
    ['Polo', 'Grey', 'L', 1, false, false, false, 299999],
    ['Polo', 'Grey', 'XL', 2, false, false, false, 299999],
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

    // Tarif longsleeve historis per 30 Oktober 2026.
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

// Validasi jumlah dan omzet.
if (
    $totalStadium !== 72 ||
    $totalSupporter !== 6 ||
    $totalPolo !== 4 ||
    $totalQty !== 82
) {
    throw new RuntimeException(
        "Validasi jumlah gagal: Stadium={$totalStadium}, " .
        "Supporter={$totalSupporter}, Polo={$totalPolo}, Total={$totalQty}."
    );
}

if (
    $totalNns !== 1 ||
    $totalPatch !== 1 ||
    $totalLongsleeve !== 6
) {
    throw new RuntimeException(
        "Validasi opsi gagal: NNS={$totalNns}, " .
        "Patch={$totalPatch}, L/S={$totalLongsleeve}."
    );
}

if ($subtotal !== 28399918) {
    throw new RuntimeException(
        "Validasi omzet gagal: Rp" . number_format($subtotal, 0, ',', '.')
    );
}

echo "====================================\n";
echo "IMPORT TRANSAKSI STADIUM 30 OKTOBER\n";
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
        'shipping_name' => 'Transaksi Historis 30 Oktober 2026',
    ]);

    foreach ($items as $item) {
        $transaction->items()->create($item);
    }
});

echo "\nIMPORT BERHASIL.\n";
