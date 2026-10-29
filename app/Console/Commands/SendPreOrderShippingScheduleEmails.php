<?php

namespace App\Console\Commands;

use App\Mail\PreOrderShippingScheduleMail;
use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Models\TransactionNotification;
use App\Services\EmailArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SendPreOrderShippingScheduleEmails extends Command
{
    protected $signature = 'emails:pre-order-shipping-schedule
                            {--send : Kirim email setelah daftar penerima diperiksa}
                            {--include-legacy : Sertakan item legacy yang semua variannya cocok dengan PO terkonfirmasi}';

    protected $description = 'Preview atau kirim email jadwal pengiriman PO mulai 3 November 2026';

    private const NOTIFICATION_TYPE = 'PRE_ORDER_SHIPPING_SCHEDULE_EMAIL';

    public function handle(): int
    {
        $transactions = Transaction::query()
            ->where('status', Transaction::ORDER_PROCESSING)
            ->whereNotNull('paid_at')
            ->whereNotNull('shipping_email')
            ->where('shipping_email', '<>', '')
            ->with([
                'items.fulfillment',
                'items.productVariant.product',
                'items.productVariant.size',
                'items.productVariant.color',
                'fulfillments.shipments',
                'shipments',
                'notifications',
                'orderStatusHistories',
            ])
            ->orderBy('id')
            ->get();

        // Referensi varian diambil dari item fulfillment PO aktif yang
        // transaksinya belum terindikasi dikirim.
        $confirmedVariantKeys = [];

        foreach ($transactions as $candidateTransaction) {
            // Referensi harus berasal dari transaksi yang juga lolos
            // pemeriksaan penerima pada command utama.
            if (! filter_var(
                trim((string) $candidateTransaction->shipping_email),
                FILTER_VALIDATE_EMAIL
            )) {
                continue;
            }

            $alreadyNotified = $candidateTransaction->notifications->contains(
                fn ($notification) =>
                    $notification->type === self::NOTIFICATION_TYPE
                    && $notification->sent_at !== null
            );

            if ($alreadyNotified) {
                continue;
            }

            $activeFulfillments = $candidateTransaction->fulfillments
                ->filter(fn ($fulfillment) =>
                    $fulfillment->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                    && $fulfillment->status !== TransactionFulfillment::STATUS_CANCELLED
                );

            if (
                $activeFulfillments->isEmpty()
                || $this->hasAlreadyShipped(
                    $candidateTransaction,
                    $activeFulfillments,
                    true
                )
            ) {
                continue;
            }

            foreach ($candidateTransaction->items as $candidateItem) {
                $fulfillment = $candidateItem->fulfillment;

                if (
                    ! $fulfillment
                    || $fulfillment->group_type !== TransactionFulfillment::GROUP_PRE_ORDER
                    || $fulfillment->status === TransactionFulfillment::STATUS_CANCELLED
                ) {
                    continue;
                }

                $variant = $candidateItem->productVariant;

                if (! $variant) {
                    continue;
                }

                $productId = $variant->product_id;
                $confirmedVariantKeys[] = 'variant:' . $productId . ':' . $variant->id;

                if (filled($variant->sku)) {
                    $confirmedVariantKeys[] = 'sku:' . $productId . ':' . trim($variant->sku);
                }
            }
        }

        $confirmedVariantKeys = array_fill_keys(array_unique($confirmedVariantKeys), true);

        $recipients = [];
        $excluded = [
            'email_invalid' => 0,
            'already_notified' => 0,
            'already_shipped' => 0,
            'no_po_items' => 0,
        ];

        foreach ($transactions as $transaction) {
            if (! filter_var(trim((string) $transaction->shipping_email), FILTER_VALIDATE_EMAIL)) {
                $excluded['email_invalid']++;
                continue;
            }

            $alreadyNotified = $transaction->notifications->contains(
                fn ($notification) =>
                    $notification->type === self::NOTIFICATION_TYPE
                    && $notification->sent_at !== null
            );

            if ($alreadyNotified) {
                $excluded['already_notified']++;
                continue;
            }

            $preOrderFulfillments = $transaction->fulfillments
                ->filter(fn ($fulfillment) =>
                    $fulfillment->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                    && $fulfillment->status !== TransactionFulfillment::STATUS_CANCELLED
                );

            $hasActivePoFulfillment = $preOrderFulfillments->isNotEmpty();

            if ($this->hasAlreadyShipped($transaction, $preOrderFulfillments, $hasActivePoFulfillment)) {
                $excluded['already_shipped']++;
                continue;
            }

            $preOrderItems = $transaction->items
                ->filter(function ($item) {
                    $fulfillment = $item->fulfillment;

                    // Jika item sudah memiliki fulfillment, hanya fulfillment PO aktif yang dihitung.
                    if ($fulfillment) {
                        return $fulfillment->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                            && $fulfillment->status !== TransactionFulfillment::STATUS_CANCELLED;
                    }

                    // Item lama tanpa fulfillment hanya dapat diidentifikasi dari status produk saat ini.
                    return $item->productVariant?->product?->availability === 'pre_order';
                })
                ->values();

            if ($preOrderItems->isEmpty()) {
                $excluded['no_po_items']++;
                continue;
            }

            $confirmedItems = $preOrderItems
                ->filter(fn ($item) =>
                    $item->fulfillment?->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                    && $item->fulfillment?->status !== TransactionFulfillment::STATUS_CANCELLED
                )
                ->values();

            $legacyItems = $preOrderItems
                ->filter(fn ($item) => $item->fulfillment === null)
                ->values();

            $itemsForEmail = $confirmedItems;

            // Sertakan seluruh item legacy PO yang teridentifikasi jika diminta.
            if ($this->option('include-legacy')) {
                $itemsForEmail = $confirmedItems->concat($legacyItems)->values();
            }

            if ($itemsForEmail->isEmpty()) {
                continue;
            }

            $recipients[] = [
                'transaction' => $transaction,
                'items' => $itemsForEmail,
                'confirmed_count' => $confirmedItems->count(),
                'legacy_count' => $legacyItems->count(),
            ];
        }

        $this->newLine();
        $this->info('Jadwal pengiriman PO: mulai 3 November 2026');
        $this->info('Mode: ' . ($this->option('send') ? 'KIRIM EMAIL' : 'PREVIEW'));
        $this->info('Filter kecocokan produk + varian legacy aktif: ' . ($this->option('include-legacy') ? 'YA' : 'TIDAK'));
        $this->info('Jumlah invoice dalam daftar: ' . count($recipients));

        $this->table(
            [
                'Invoice',
                'Nama penerima',
                'Email',
                'Jumlah item PO',
            ],
            array_map(fn ($row) => [
                $row['transaction']->invoice_number,
                $row['transaction']->shipping_name,
                $row['transaction']->shipping_email,
                $row['items']->count(),
            ], $recipients)
        );

        $this->newLine();
        $this->info('Ringkasan pengecualian:');
        $this->line('Email tidak valid: ' . $excluded['email_invalid']);
        $this->line('Email jadwal sudah tercatat terkirim: ' . $excluded['already_notified']);
        $this->line('Pesanan terindikasi sudah dikirim: ' . $excluded['already_shipped']);
        $this->line('Tidak ada item PO yang teridentifikasi: ' . $excluded['no_po_items']);

        if (count($recipients) === 0) {
            $this->warn('Tidak ada penerima yang memenuhi kriteria.');
            return self::SUCCESS;
        }

        if (! $this->option('send')) {
            $this->newLine();
            $this->comment('PREVIEW SAJA: belum ada email yang dikirim.');
            $this->line('Untuk menguji ulang dengan item PO terverifikasi saja:');
            $this->line('php artisan emails:pre-order-shipping-schedule');
            $this->line('Untuk preview yang menyertakan item legacy dengan kecocokan produk + varian:');
            $this->line('php artisan emails:pre-order-shipping-schedule --include-legacy');
            return self::SUCCESS;
        }

        if ($this->option('include-legacy')) {
            $this->warn('Perhatian: item legacy disertakan hanya jika seluruh item PO legacy yang teridentifikasi cocok produk + varian dengan PO terkonfirmasi. Kecocokan ini bukan bukti historis mutlak status PO.');
        }

        if (! $this->confirm(
            'Kirim email kepada semua penerima yang tampil di daftar ini?',
            false
        )) {
            $this->warn('Pengiriman dibatalkan. Tidak ada email yang dikirim.');
            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($recipients as $row) {
            $transaction = $row['transaction'];

            try {
                $current = Transaction::query()
                    ->with([
                        'items.fulfillment',
                        'items.productVariant.product',
                'items.productVariant.size',
                'items.productVariant.color',
                        'fulfillments.shipments',
                        'shipments',
                        'notifications',
                        'orderStatusHistories',
                    ])
                    ->find($transaction->id);

                if (
                    ! $current
                    || $current->status !== Transaction::ORDER_PROCESSING
                    || $current->paid_at === null
                ) {
                    $skipped++;
                    $this->warn("Dilewati: {$transaction->invoice_number} — status transaksi berubah.");
                    continue;
                }

                if (! filter_var(trim((string) $current->shipping_email), FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    $this->warn("Dilewati: {$transaction->invoice_number} — email tidak valid.");
                    continue;
                }

                $alreadyNotified = $current->notifications->contains(
                    fn ($notification) =>
                        $notification->type === self::NOTIFICATION_TYPE
                        && $notification->sent_at !== null
                );

                if ($alreadyNotified) {
                    $skipped++;
                    $this->warn("Dilewati: {$transaction->invoice_number} — email sudah tercatat terkirim.");
                    continue;
                }

                $activePoFulfillments = $current->fulfillments
                    ->filter(fn ($fulfillment) =>
                        $fulfillment->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                        && $fulfillment->status !== TransactionFulfillment::STATUS_CANCELLED
                    );

                if ($this->hasAlreadyShipped(
                    $current,
                    $activePoFulfillments,
                    $activePoFulfillments->isNotEmpty()
                )) {
                    $skipped++;
                    $this->warn("Dilewati: {$transaction->invoice_number} — terindikasi sudah dikirim.");
                    continue;
                }

                $currentItems = $current->items
                    ->filter(function ($item) {
                        if ($item->fulfillment) {
                            return $item->fulfillment->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                                && $item->fulfillment->status !== TransactionFulfillment::STATUS_CANCELLED;
                        }

                        return $item->productVariant?->product?->availability === 'pre_order';
                    })
                    ->values();

                $currentConfirmedItems = $currentItems
                    ->filter(fn ($item) =>
                        $item->fulfillment?->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                        && $item->fulfillment?->status !== TransactionFulfillment::STATUS_CANCELLED
                    )
                    ->values();

                $currentLegacyItems = $currentItems
                    ->filter(fn ($item) => $item->fulfillment === null)
                    ->values();

                $currentItems = $currentConfirmedItems;

                if ($this->option('include-legacy')) {
                    $currentItems = $currentConfirmedItems
                        ->concat($currentLegacyItems)
                        ->values();
                }

                if ($currentItems->isEmpty()) {
                    $skipped++;
                    $this->warn("Dilewati: {$transaction->invoice_number} — item PO tidak lagi memenuhi kriteria.");
                    continue;
                }

                $notification = TransactionNotification::firstOrCreate(
                    [
                        'transaction_id' => $current->id,
                        'type' => self::NOTIFICATION_TYPE,
                    ],
                    ['sent_at' => null]
                );

                if ($notification->sent_at !== null) {
                    $skipped++;
                    $this->warn("Dilewati: {$transaction->invoice_number} — notifikasi sudah tercatat terkirim.");
                    continue;
                }

                app(EmailArchiveService::class)->send(
                    trim($current->shipping_email),
                    new PreOrderShippingScheduleMail($current, $currentItems)
                );

                $notification->update(['sent_at' => now()]);

                $sent++;
                $this->info("Terkirim: {$transaction->invoice_number}");
            } catch (Throwable $e) {
                $failed++;
                report($e);
                $this->error("Gagal: {$transaction->invoice_number} — {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Selesai. Terkirim: {$sent}; dilewati: {$skipped}; gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function matchesConfirmedPoVariant($item, array $confirmedVariantKeys): bool
    {
        $variant = $item->productVariant;

        if (! $variant) {
            return false;
        }

        $productId = $variant->product_id;
        $variantKey = 'variant:' . $productId . ':' . $variant->id;

        if (isset($confirmedVariantKeys[$variantKey])) {
            return true;
        }

        if (filled($variant->sku)) {
            $skuKey = 'sku:' . $productId . ':' . trim($variant->sku);

            return isset($confirmedVariantKeys[$skuKey]);
        }

        return false;
    }

    private function hasAlreadyShipped(
        Transaction $transaction,
        Collection $activePoFulfillments,
        bool $hasActivePoFulfillment
    ): bool {
        $shippingStatuses = ['SHIPPED', 'COMPLETED', 'DELIVERED'];

        if ($hasActivePoFulfillment) {
            // Untuk transaksi campuran, periksa pengiriman fulfillment PO saja.
            foreach ($activePoFulfillments as $fulfillment) {
                if (in_array(strtoupper((string) $fulfillment->status), $shippingStatuses, true)) {
                    return true;
                }

                if ($fulfillment->shipments->contains(
                    fn ($shipment) => in_array(
                        strtoupper((string) $shipment->status),
                        $shippingStatuses,
                        true
                    )
                )) {
                    return true;
                }
            }

            return false;
        }

        // Untuk transaksi legacy tanpa fulfillment, gunakan indikator pengiriman transaksi lama.
        if ($transaction->shipments->contains(
            fn ($shipment) => in_array(
                strtoupper((string) $shipment->status),
                $shippingStatuses,
                true
            )
        )) {
            return true;
        }

        if ($transaction->orderStatusHistories->contains(
            fn ($history) => strtoupper((string) $history->status) === Transaction::ORDER_SHIPPED
        )) {
            return true;
        }

        foreach ([
            $transaction->tracking_number,
            $transaction->biteship_tracking_id,
            $transaction->biteship_waybill_id,
        ] as $trackingValue) {
            if (filled($trackingValue)) {
                return true;
            }
        }

        $biteshipStatus = strtoupper((string) $transaction->biteship_status);

        foreach (['PICKED', 'IN_TRANSIT', 'DROPPING_OFF', 'DELIVERED', 'COMPLETED', 'SHIPPED'] as $status) {
            if ($biteshipStatus !== '' && str_contains($biteshipStatus, $status)) {
                return true;
            }
        }

        return false;
    }
}
