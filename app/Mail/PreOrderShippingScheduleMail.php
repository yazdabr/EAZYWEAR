<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreOrderShippingScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public Collection $preOrderItems,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Jadwal Pengiriman Pre-order - '
                . $this->transaction->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pre-order-shipping-schedule',
            with: [
                'transaction' => $this->transaction,
                'preOrderItems' => $this->preOrderItems,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
