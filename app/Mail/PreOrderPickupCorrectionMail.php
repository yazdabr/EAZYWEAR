<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreOrderPickupCorrectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public array $orders,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Koreksi Jadwal Pengambilan Pre-Order Eazywear',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pre-order-pickup-correction',
            with: ['orders' => $this->orders, 'customerName' => $this->customerName],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
