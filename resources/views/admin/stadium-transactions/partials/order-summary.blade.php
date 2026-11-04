@php
    $versionOrder = [
        'Stadium' => 0,
        'Supporter' => 1,
        'Produk' => 2,
    ];

    $kitOrder = [
        'Home' => 0,
        'Away' => 1,
        'Third' => 2,
    ];

    $summaryGroups = [];

    foreach ($transaction->items as $item) {
        $name = $item->productVariant?->product?->name ?? '';

        preg_match('/\b(Supporter|Stadium)\b/i', $name, $versionMatch);
        preg_match('/\b(Home|Away|Third)\b/i', $name, $kitMatch);

        $version = isset($versionMatch[1])
            ? ucfirst(strtolower($versionMatch[1]))
            : 'Produk';

        $kit = isset($kitMatch[1])
            ? ucfirst(strtolower($kitMatch[1]))
            : $name;

        $key = $version . '|' . $kit;

        if (!isset($summaryGroups[$key])) {
            $summaryGroups[$key] = [
                'version' => $version,
                'kit' => $kit,
                'qty' => 0,
            ];
        }

        $summaryGroups[$key]['qty'] += (int) $item->qty;
    }

    $summaryGroups = collect($summaryGroups)
        ->sortBy(function ($group) use ($versionOrder, $kitOrder) {
            return sprintf(
                '%03d|%03d|%s',
                $versionOrder[$group['version']] ?? 99,
                $kitOrder[$group['kit']] ?? 99,
                $group['kit']
            );
        });

    $groupedOrders = $summaryGroups
        ->groupBy('version')
        ->sortBy(fn ($products, $version) => $versionOrder[$version] ?? 99);

    $totalUniqueProducts = $summaryGroups->count();
    $totalQty = $summaryGroups->sum('qty');
@endphp

@foreach ($groupedOrders as $version => $products)
    @php
        $kits = $products->pluck('kit')->unique()->values();

        $labels = $kits->count() > 1
            ? $kits->slice(0, -1)->implode(', ') . ' & ' . $kits->last()
            : $kits->first();

        $groupLabel = $version === 'Produk'
            ? $labels
            : $version . ' ' . $labels;
    @endphp

    <div class="mb-2 last:mb-0">
        <p class="font-medium text-slate-700">{{ $groupLabel }}</p>
        <p class="text-xs text-slate-500">
            {{ $products->sum('qty') }} pcs | {{ $products->count() }} jenis produk
        </p>
    </div>
@endforeach

<p class="text-xs text-slate-500">
    Total {{ $totalUniqueProducts }} jenis produk | {{ $totalQty }} pcs
</p>
