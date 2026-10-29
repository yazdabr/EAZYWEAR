<?php

namespace App\Console\Commands;

use App\Mail\PreOrderPickupCorrectionMail;
use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Models\TransactionNotification;
use App\Services\EmailArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class SendPreOrderPickupCorrectionEmails extends Command
{
    private const ORIGINAL_TYPE = 'PRE_ORDER_SHIPPING_SCHEDULE_EMAIL';
    private const CORRECTION_TYPE = 'PRE_ORDER_PICKUP_CORRECTION_EMAIL';

    protected $signature = 'emails:pre-order-pickup-correction
                            {--send : Kirim email setelah preview diperiksa}
                            {--limit=0 : Batasi jumlah alamat email unik (0 = tanpa batas)}';

    protected $description = 'Preview atau kirim email koreksi jadwal pickup pre-order Eazywear';

    public function handle(EmailArchiveService $emailArchive): int
    {
        $transactions = Transaction::query()
            ->whereHas('notifications', fn ($q) => $q
                ->where('type', self::ORIGINAL_TYPE)
                ->whereNotNull('sent_at'))
            ->with([
                'fulfillments',
                'items.fulfillment',
                'items.productVariant.product',
                'notifications',
            ])
            ->orderBy('id')
            ->get();

        $recipients = [];

        foreach ($transactions as $transaction) {
            $email = trim((string) $transaction->shipping_email);

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $correctionSent = $transaction->notifications->contains(
                fn ($notification) =>
                    $notification->type === self::CORRECTION_TYPE
                    && $notification->sent_at !== null
            );

            if ($correctionSent) {
                continue;
            }

            $schedules = [];

            $addSchedule = function ($date, $start, $end) use (&$schedules): void {
                $dateText = $date
                    ? Carbon::parse($date)->format('d-m-Y')
                    : 'Belum ditentukan';

                $startText = $start ? substr((string) $start, 0, 5) : null;
                $endText = $end ? substr((string) $end, 0, 5) : null;

                $timeText = $startText && $endText
                    ? "{$startText}-{$endText}"
                    : ($startText ?: 'Jam belum ditentukan');

                $key = $dateText . '|' . $timeText;

                $schedules[$key] = [
                    'date' => $dateText,
                    'time' => $timeText,
                ];
            };

            foreach ($transaction->fulfillments as $fulfillment) {
                if (
                    $fulfillment->group_type === TransactionFulfillment::GROUP_PRE_ORDER
                    && $fulfillment->status !== TransactionFulfillment::STATUS_CANCELLED
                    && $fulfillment->shipping_method === 'Ambil di Tempat'
                ) {
                    $addSchedule(
                        $fulfillment->pickup_date,
                        $fulfillment->pickup_time_start,
                        $fulfillment->pickup_time_end
                    );
                }
            }

            $hasLegacyPoItem = $transaction->items->contains(
                fn ($item) =>
                    $item->fulfillment === null
                    && $item->productVariant?->product?->availability === 'pre_order'
            );

            if (
                $hasLegacyPoItem
                && $transaction->shipping_method === 'Ambil di Tempat'
            ) {
                $addSchedule(
                    $transaction->pickup_date,
                    $transaction->pickup_time_start,
                    $transaction->pickup_time_end
                );
            }

            if ($schedules === []) {
                continue;
            }

            $emailKey = strtolower($email);

            if (! isset($recipients[$emailKey])) {
                $recipients[$emailKey] = [
                    'email' => $email,
                    'name' => $transaction->shipping_name ?: 'Pelanggan Eazywear',
                    'transactions' => [],
                    'orders' => [],
                ];
            }

            $recipients[$emailKey]['transactions'][$transaction->id] = $transaction;

            $recipients[$emailKey]['orders'][$transaction->invoice_number] = [
                'invoice' => $transaction->invoice_number,
                'schedules' => array_values($schedules),
            ];
        }

        $recipients = array_values($recipients);
        $totalRecipients = count($recipients);
        $limit = max(0, (int) $this->option('limit'));

        if ($limit > 0) {
            $recipients = array_slice($recipients, 0, $limit);
        }

        $this->newLine();
        $this->info('Periode pengambilan: 30 Oktober - 3 November 2026');
        $this->info('Mode: ' . ($this->option('send') ? 'KIRIM EMAIL' : 'PREVIEW'));
        $this->info('Alamat email unik eligible: ' . $totalRecipients);
        $this->info('Alamat email dalam batch ini: ' . count($recipients));

        $this->table(
            ['Nama', 'Email', 'Invoice', 'Jumlah Invoice', 'Jadwal Pickup'],
            array_map(function ($row) {
                $scheduleRows = [];

                foreach ($row['orders'] as $order) {
                    foreach ($order['schedules'] as $schedule) {
                        $scheduleRows[] = $order['invoice'] . ': '
                            . $schedule['date'] . ' ' . $schedule['time'];
                    }
                }

                return [
                    $row['name'],
                    $row['email'],
                    implode(', ', array_keys($row['orders'])),
                    count($row['orders']),
                    implode('; ', array_unique($scheduleRows)),
                ];
            }, $recipients)
        );

        if (! $this->option('send')) {
            $this->comment('PREVIEW SAJA: belum ada email yang dikirim.');
            return self::SUCCESS;
        }

        if ($recipients === []) {
            $this->warn('Tidak ada penerima koreksi yang memenuhi kriteria.');
            return self::SUCCESS;
        }

        if (! $this->confirm('Kirim email koreksi kepada penerima di atas?', false)) {
            $this->warn('Pengiriman dibatalkan. Tidak ada email yang dikirim.');
            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($recipients as $row) {
            try {
                $pending = [];

                foreach ($row['transactions'] as $transaction) {
                    $current = Transaction::with('notifications')->find($transaction->id);

                    if (! $current) {
                        continue;
                    }

                    $originalSent = $current->notifications->contains(
                        fn ($notification) =>
                            $notification->type === self::ORIGINAL_TYPE
                            && $notification->sent_at !== null
                    );

                    if (! $originalSent) {
                        continue;
                    }

                    $notification = TransactionNotification::firstOrCreate(
                        [
                            'transaction_id' => $current->id,
                            'type' => self::CORRECTION_TYPE,
                        ],
                        ['sent_at' => null]
                    );

                    if ($notification->sent_at !== null) {
                        continue;
                    }

                    $pending[] = [
                        'transaction' => $current,
                        'notification' => $notification,
                    ];
                }

                if ($pending === []) {
                    $skipped++;
                    $this->warn("Dilewati: {$row['email']} - tidak ada invoice yang masih perlu dikoreksi.");
                    continue;
                }

                $orders = [];

                foreach ($pending as $item) {
                    $invoice = $item['transaction']->invoice_number;

                    if (isset($row['orders'][$invoice])) {
                        $orders[] = $row['orders'][$invoice];
                    }
                }

                if ($orders === [] || count($orders) !== count($pending)) {
                    throw new \RuntimeException(
                        "Detail invoice pending tidak cocok dengan jadwal pickup untuk {$row['email']}."
                    );
                }

                $emailArchive->send(
                    $row['email'],
                    new PreOrderPickupCorrectionMail($row['name'], $orders)
                );

                foreach ($pending as $item) {
                    $notification = $item['notification'];
                    $notification->sent_at = now();
                    $notification->save();
                }

                $sent++;
                $this->info("Terkirim: {$row['email']} (" . count($pending) . ' invoice)');
            } catch (Throwable $e) {
                $failed++;
                report($e);
                $this->error("Gagal: {$row['email']} - {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Selesai. Terkirim: {$sent}; dilewati: {$skipped}; gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
