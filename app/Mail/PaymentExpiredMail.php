<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Kadaluarsa - '
                . $this->transaction->invoice_number,
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-expired',
            with: [
                'transaction' => $this->transaction,
            ],
        );
    }


    public function attachments(): array
    {
        return [];
    }
}