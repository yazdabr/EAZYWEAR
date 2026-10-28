
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

$invoice = 'STADIUM-LEGACY-20261010';
$date = '2026-10-10 12:00:00';

$rows = [
    // STADIUM HOME — reguler dan L/S
    ['Stadium', 'Home', 'XS', 1, false, false, false],
    ['Stadium', 'Home', 'S', 3, false, false, false],
    ['Stadium', 'Home', 'M', 4, false, false, false],
    ['Stadium', 'Home', 'L', 2, false, false, false],
    ['Stadium', 'Home', 'L', 2, true, false, false],
    ['Stadium', 'Home', 'XL', 5, false, false, false],
    ['Stadium', 'Home', 'XL', 1, true, false, false],
    ['Stadium', 'Home', 'XXL', 3, false, false, false],
    ['Stadium', 'Home', '3XL', 1, false, false, false],
    ['Stadium', 'Home', '4XL', 1, false, false, false],
    ['Stadium', 'Home', '5XL', 2, false, false, false],

    // STADIUM HOME — NNS + patch
    ['Stadium', 'Home', 'L', 1, false, true, true],

    // STADIUM AWAY — reguler dan L/S
    ['Stadium', 'Away', 'S', 1, false, false, false],
    ['Stadium', 'Away', 'M', 1, true, false, false],

    // SUPPORTER HOME
    ['Supporter', 'Home', 'XS', 1, false, false, false],
    ['Supporter', 'Home', 'M', 7, false, false, false],
    ['Supporter', 'Home', 'L', 8, false, false, false],
    ['Supporter', 'Home', 'XL', 4, false, false, false],
    ['Supporter', 'Home', 'XXL', 1, false, false, false],

    // SUPPORTER AWAY
    ['Supporter', 'Away', 'XS', 3, false, false, false],
    ['Supporter', 'Away', 'S', 1, false, false, false],
    ['Supporter', 'Away', 'M', 4, false, false, false],
    ['Supporter', 'Away', 'L', 11, false, false, false],
    ['Supporter', 'Away', 'XL', 5, false, false, false],
    ['Supporter', 'Away', 'XXL', 6, false, false, false],
    ['Supporter', 'Away', '4XL', 1, false, false, false],

    // SUPPORTER THIRD
    ['Supporter', 'Third', 'XS', 2, false, false, false],
    ['Supporter', 'Third', 'S', 3, false, false, false],
    ['Supporter', 'Third', 'M', 8, false, false, false],
    ['Supporter', 'Third', 'L', 5, false, false, false],
    ['Supporter', 'Third', 'XL', 6, false, false, false],
    ['Supporter', 'Third', 'XXL', 3, false, false, false],
    ['Supporter', 'Third', '3XL', 1, false, false, false],
    ['Supporter', 'Third', '4XL', 1, false, false, false],
];

// Polo Jersey: ukuran L 2 pcs dan 3XL 1 pcs.
$poloRows = [
    ['Polo', 'Grey', 'L', 2],
    ['Polo', 'Grey', '3XL', 1],
];

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

foreach ($rows as [$version, $kit, $size, $qty, $longsleeve, $nns, $patch]) {
    $variant = ProductVariant::query()
        ->with(['product', 'size'])
        ->whereHas('product', fn ($q) => $q
            ->where('name', 'like', '%' . $version . ' Version 2026/27%')
            ->where('name', 'like', '%' . $kit))
        ->whereHas('size', fn ($q) => $q->where('name', $size))
        ->first();

    if (!$variant) {
        throw new RuntimeException("Varian tidak ditemukan: {$version} {$kit}, ukuran {$size}.");
    }

    $product = $variant->product;

    if ($nns && !$product->customization_enabled) {
        throw new RuntimeException("Customization tidak aktif: {$product->name}");
    }
    if ($patch && !$product->patch_enabled) {
        throw new RuntimeException("Patch tidak aktif: {$product->name}");
    }
    if ($longsleeve && !$product->longsleeve_enabled) {
        throw new RuntimeException("L/S tidak aktif: {$product->name}");
    }

    $basePrice = (float) $variant->price;
    $customizationFee = $nns ? (int) $product->customization_price : 0;
    $longsleevePrice = $longsleeve ? (int) $product->longsleeve_price : 0;
    $patchPrice = $patch ? (int) $product->patch_price : 0;
    $finalPrice = $basePrice + $customizationFee + $longsleevePrice + $patchPrice;
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
        'custom_name' => $nns ? 'NNS HISTORIS' : null,
        'custom_number' => null,
        'is_longsleeve' => $longsleeve,
        'longsleeve_price' => $longsleevePrice,
        'is_patch' => $patch,
        'patch_price' => $patchPrice,
    ];
}

foreach ($poloRows as [$type, $color, $size, $qty]) {
    $variant = ProductVariant::query()
        ->with(['product', 'size'])
        ->where('product_id', 56)
        ->whereHas('size', fn ($q) => $q->where('name', $size))
        ->first();

    if (!$variant) {
        throw new RuntimeException("Varian Polo Jersey tidak ditemukan: {$size}.");
    }

    $price = (float) $variant->price;
    $lineSubtotal = $price * $qty;
    $subtotal += $lineSubtotal;
    $totalPolo += $qty;

    $items[] = [
        'product_variant_id' => $variant->id,
        'qty' => $qty,
        'price' => $price,
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

$totalQty = $totalStadium + $totalSupporter + $totalPolo;

if ($totalStadium !== 28 || $totalSupporter !== 81 || $totalPolo !== 3 || $totalQty !== 112) {
    throw new RuntimeException(
        "Validasi jumlah gagal: Stadium={$totalStadium}, Supporter={$totalSupporter}, Polo={$totalPolo}, Total={$totalQty}."
    );
}

echo "INVOICE: {$invoice}\n";
echo "TANGGAL: {$date}\n";
echo "STADIUM: {$totalStadium} pcs\n";
echo "SUPPORTER: {$totalSupporter} pcs\n";
echo "POLO: {$totalPolo} pcs\n";
echo "TOTAL: {$totalQty} pcs\n";
echo "ITEM NNS: {$totalNns} pcs\n";
echo "ITEM PATCH: {$totalPatch} pcs\n";
echo "ITEM L/S: {$totalLongsleeve} pcs\n";
echo "SUBTOTAL: Rp" . number_format($subtotal, 0, ',', '.') . "\n";
echo "STOK: tidak diubah oleh script ini.\n";

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
        'shipping_name' => 'Transaksi Historis 10 Oktober 2026',
    ]);

    foreach ($items as $item) {
        $transaction->items()->create($item);
    }
});

echo "\nIMPORT BERHASIL.\n";
