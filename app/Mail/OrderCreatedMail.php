<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Services\QrisQrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Eazywear Berhasil Dibuat - '
                . $this->transaction->invoice_number,
        );
    }

    public function content(): Content
    {
        $qrisImageCid = null;

        if (
            $this->transaction->payment_method === 'QRIS'
            && filled($this->transaction->qris_content)
        ) {
            try {
                if (!extension_loaded('imagick')) {
                    throw new \RuntimeException(
                        'Ekstensi Imagick tidak tersedia.'
                    );
                }

                $qrisPng = app(QrisQrCodeService::class)->generatePng(
                    $this->transaction->qris_content
                );

                $qrisImageCid = 'qris-' . $this->transaction->getKey()
                    . '@eazywear.id';

                $this->withSymfonyMessage(
                    function (Email $message) use ($qrisPng, $qrisImageCid) {
                        $message->addPart(
                            (new DataPart(
                                $qrisPng,
                                'qris.png',
                                'image/png'
                            ))
                                ->asInline()
                                ->setContentId($qrisImageCid)
                        );
                    }
                );
            } catch (\Throwable $exception) {
                Log::warning(
                    'Gagal membuat QRIS PNG untuk email pesanan.',
                    [
                        'transaction_id' => $this->transaction->getKey(),
                        'invoice_number' => $this->transaction->invoice_number,
                        'error' => $exception->getMessage(),
                    ]
                );

                $qrisImageCid = null;
            }
        }

        return new Content(
            view: 'emails.order-created',
            with: [
                'transaction' => $this->transaction,
                'qrisImageCid' => $qrisImageCid,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}