<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'STADIUM-LEGACY-20261004';
$date = '2026-10-04 12:00:00';

$rows = [
    // STADIUM HOME â€” reguler
    ['Stadium', 'Home', 'XS', 1, false, false, false],
    ['Stadium', 'Home', 'S', 1, false, false, false],
    ['Stadium', 'Home', 'M', 13, false, false, false],
    ['Stadium', 'Home', 'M', 1, true, false, false],
    ['Stadium', 'Home', 'L', 21, false, false, false],
    ['Stadium', 'Home', 'L', 2, true, false, false],
    ['Stadium', 'Home', 'XL', 18, false, false, false],
    ['Stadium', 'Home', 'XL', 1, true, false, false],
    ['Stadium', 'Home', 'XXL', 6, false, false, false],
    ['Stadium', 'Home', '3XL', 4, false, false, false],
    ['Stadium', 'Home', '4XL', 5, false, false, false],

    // STADIUM HOME â€” NNS dan patch
    ['Stadium', 'Home', 'XS', 1, false, true, false],
    ['Stadium', 'Home', 'M', 1, false, true, true],
    ['Stadium', 'Home', 'L', 1, false, true, true],
    ['Stadium', 'Home', 'XL', 1, false, true, true],

    // STADIUM AWAY â€” reguler
    ['Stadium', 'Away', 'XS', 3, false, false, false],
    ['Stadium', 'Away', 'S', 3, false, false, false],
    ['Stadium', 'Away', 'M', 12, false, false, false],
    ['Stadium', 'Away', 'M', 2, true, false, false],
    ['Stadium', 'Away', 'L', 12, false, false, false],
    ['Stadium', 'Away', 'L', 3, true, false, false],
    ['Stadium', 'Away', 'XL', 10, false, false, false],
    ['Stadium', 'Away', 'XL', 2, true, false, false],
    ['Stadium', 'Away', 'XXL', 4, false, false, false],
    ['Stadium', 'Away', '3XL', 4, false, false, false],

    // STADIUM AWAY â€” NNS dan patch
    ['Stadium', 'Away', 'S', 2, false, true, false],
    ['Stadium', 'Away', 'XL', 1, false, true, true],
    ['Stadium', 'Away', 'XL', 1, true, true, true],

    // STADIUM THIRD â€” reguler
    ['Stadium', 'Third', 'M', 6, false, false, false],
    ['Stadium', 'Third', 'L', 14, false, false, false],
    ['Stadium', 'Third', 'L', 2, true, false, false],
    ['Stadium', 'Third', 'XL', 9, false, false, false],
    ['Stadium', 'Third', 'XXL', 3, false, false, false],
    ['Stadium', 'Third', 'XXL', 1, true, false, false],
    ['Stadium', 'Third', '3XL', 4, false, false, false],

    // STADIUM THIRD â€” NNS dan patch
    ['Stadium', 'Third', 'XL', 1, false, true, true],

    // SUPPORTER HOME
    ['Supporter', 'Home', 'XS', 4, false, false, false],
    ['Supporter', 'Home', 'S', 8, false, false, false],
    ['Supporter', 'Home', 'M', 10, false, false, false],
    ['Supporter', 'Home', 'L', 5, false, false, false],
    ['Supporter', 'Home', 'XL', 10, false, false, false],
    ['Supporter', 'Home', 'XXL', 7, false, false, false],
    ['Supporter', 'Home', '3XL', 1, false, false, false],
    ['Supporter', 'Home', '4XL', 2, false, false, false],
    ['Supporter', 'Home', '5XL', 1, false, false, false],

    // SUPPORTER AWAY
    ['Supporter', 'Away', 'S', 6, false, false, false],
    ['Supporter', 'Away', 'M', 2, false, false, false],
    ['Supporter', 'Away', 'L', 10, false, false, false],
    ['Supporter', 'Away', 'XL', 5, false, false, false],
    ['Supporter', 'Away', 'XXL', 1, false, false, false],
    ['Supporter', 'Away', '3XL', 2, false, false, false],

    // SUPPORTER THIRD
    ['Supporter', 'Third', 'XS', 2, false, false, false],
    ['Supporter', 'Third', 'S', 1, false, false, false],
    ['Supporter', 'Third', 'M', 1, false, false, false],
    ['Supporter', 'Third', 'L', 11, false, false, false],
    ['Supporter', 'Third', 'XL', 2, false, false, false],
    ['Supporter', 'Third', 'XXL', 2, false, false, false],
    ['Supporter', 'Third', '3XL', 3, false, false, false],
    ['Supporter', 'Third', '4XL', 2, false, false, false],
];

// Pastikan invoice belum pernah diimpor.
if (Transaction::where('invoice_number', $invoice)->exists()) {
    throw new RuntimeException("Invoice {$invoice} sudah ada.");
}

$items = [];
$subtotal = 0;
$totalStadium = 0;
$totalSupporter = 0;
$totalNns = 0;
$totalPatch = 0;
$totalLongsleeve = 0;

foreach ($rows as [$version, $kit, $size, $qty, $longsleeve, $nns, $patch]) {
    $name = "PS Barito Putera {$version} Version 2026/27";

    $variant = ProductVariant::query()
        ->with(['product', 'size'])
        ->whereHas('product', fn ($q) => $q->where('name', 'like', '%' . $version . ' Version 2026/27%')->where('name', 'like', '%' . $kit))
        ->whereHas('size', fn ($q) => $q->where('name', $size))
        ->first();

    if (!$variant) {
        throw new RuntimeException(
            "Varian tidak ditemukan: {$name}, ukuran {$size}."
        );
    }

    $product = $variant->product;

    if ($nns && !$product->customization_enabled) {
        throw new RuntimeException("Customization tidak aktif: {$name}");
    }

    if ($patch && !$product->patch_enabled) {
        throw new RuntimeException("Patch tidak aktif: {$name}");
    }

    if ($longsleeve && !$product->longsleeve_enabled) {
        throw new RuntimeException("L/S tidak aktif: {$name}");
    }

    $basePrice = (float) $variant->price;
    $customizationFee = $nns
        ? (int) $product->customization_price
        : 0;
    $longsleevePrice = $longsleeve
        ? (int) $product->longsleeve_price
        : 0;
    $patchPrice = $patch
        ? (int) $product->patch_price
        : 0;

    // Harga final per pcs, mengikuti pola CheckoutController.
    $finalPrice = $basePrice
        + $customizationFee
        + $longsleevePrice
        + $patchPrice;

    $lineSubtotal = $finalPrice * $qty;

    $subtotal += $lineSubtotal;
    $totalNns += $nns ? $qty : 0;
    $totalPatch += $patch ? $qty : 0;
    $totalLongsleeve += $longsleeve ? $qty : 0;

    if ($version === 'Stadium') {
        $totalStadium += $qty;
    } else {
        $totalSupporter += $qty;
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

$totalQty = $totalStadium + $totalSupporter;

if ($totalStadium !== 176 || $totalSupporter !== 98 || $totalQty !== 274) {
    throw new RuntimeException(
        "Validasi jumlah gagal: Stadium={$totalStadium}, " .
        "Supporter={$totalSupporter}, Total={$totalQty}."
    );
}

echo "INVOICE: {$invoice}\n";
echo "TANGGAL: {$date}\n";
echo "STADIUM: {$totalStadium} pcs\n";
echo "SUPPORTER: {$totalSupporter} pcs\n";
echo "TOTAL: {$totalQty} pcs\n";
echo "ITEM NNS: {$totalNns} pcs\n";
echo "ITEM PATCH: {$totalPatch} pcs\n";
echo "ITEM L/S: {$totalLongsleeve} pcs\n";
echo "SUBTOTAL: Rp" . number_format($subtotal, 0, ',', '.') . "\n";
echo "STOK: tidak diubah oleh script ini.\n";

if (!in_array('--import', $argv, true)) {
    echo "\nDRY RUN: belum ada transaksi yang disimpan.\n";
    echo "Periksa hasil di atas sebelum menjalankan dengan --import.\n";
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
        'shipping_name' => 'Transaksi Historis 4 Oktober 2026',
    ]);

    foreach ($items as $item) {
        $transaction->items()->create($item);
    }
});

echo "\nIMPORT BERHASIL.\n";
