$ErrorActionPreference = 'Stop'

$file = 'resources/views/admin/offline-transactions/index.blade.php'
$content = [System.IO.File]::ReadAllText((Resolve-Path $file))

$pattern = '(?s)@foreach \(\$transaction->items as \$item\).*?@endforeach'

$replacement = @'
@if (str_starts_with($transaction->invoice_number ?? '', 'BOOTH-LEGACY-'))
    @php
        $legacyGroups = [
            'Stadium Home, Away & Third' => ['qty' => 0, 'items' => 0],
            'Supporter Home, Away & Third' => ['qty' => 0, 'items' => 0],
        ];

        foreach ($transaction->items as $item) {
            $name = $item->productVariant?->product?->name ?? '';
            $group = str_contains($name, 'Supporter')
                ? 'Supporter Home, Away & Third'
                : 'Stadium Home, Away & Third';

            $legacyGroups[$group]['qty'] += (int) $item->qty;
            $legacyGroups[$group]['items']++;
        }
    @endphp

    @foreach ($legacyGroups as $groupName => $group)
        @if ($group['items'] > 0)
            <div class="mb-2">
                <p class="font-medium text-slate-700">{{ $groupName }}</p>
                <p class="text-xs text-slate-500">
                    {{ $group['qty'] }} pcs | {{ $group['items'] }} jenis produk
                </p>
            </div>
        @endif
    @endforeach

    <p class="text-xs text-slate-500">
        Total {{ $transaction->items->count() }} jenis produk | {{ $transaction->items->sum('qty') }} pcs
    </p>
@else
    @foreach ($transaction->items as $item)
        <div class="mb-2 last:mb-0">
            <p class="font-medium text-slate-700">
                {{ $item->productVariant?->product?->name ?? 'Produk' }}
                @if ($item->productVariant?->size?->name)
                    ({{ $item->productVariant->size->name }})
                @endif
            </p>
            <p class="text-xs text-slate-500">
                {{ $item->qty }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}
            </p>
        </div>
    @endforeach
@endif
'@

$count = ([regex]::Matches($content, $pattern)).Count
if ($count -ne 2) {
    throw "Dibatalkan: ditemukan $count blok, seharusnya 2. File tidak diubah."
}

$content = [regex]::Replace($content, $pattern, $replacement)

[System.IO.File]::WriteAllText(
    (Resolve-Path $file),
    $content,
    [System.Text.UTF8Encoding]::new($false)
)

Write-Host 'Berhasil: tampilan mobile dan desktop diperbarui.'