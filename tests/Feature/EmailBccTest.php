<?php

namespace Tests\Feature;

use App\Mail\OrderCompletedMail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderProcessingMail;
use App\Mail\OrderShippedMail;
use App\Mail\PaymentConfirmedMail;
use App\Mail\PaymentExpiredMail;
use App\Mail\PickupCompletedMail;
use App\Mail\PreOrderPickupCorrectionMail;
use App\Mail\PreOrderShippingScheduleMail;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailBccTest extends TestCase
{
    public function test_payment_confirmed_email_has_orders_bcc(): void
    {
        $transaction = new Transaction([
            'invoice_number' => 'INV-BCC-PAYMENT',
        ]);

        $mail = new PaymentConfirmedMail($transaction);

        $this->assertTrue($mail->hasBcc('orders@eazywear.id'));

        $bcc = $mail->envelope()->bcc;

        $this->assertCount(1, $bcc);
        $this->assertSame('orders@eazywear.id', $bcc[0]->address);
    }

    public function test_payment_confirmed_bcc_is_preserved_when_queued(): void
    {
        Mail::fake();

        $transaction = new Transaction([
            'invoice_number' => 'INV-BCC-QUEUED',
        ]);

        Mail::to('customer@example.com')
            ->queue(new PaymentConfirmedMail($transaction));

        Mail::assertQueued(
            PaymentConfirmedMail::class,
            function (PaymentConfirmedMail $mail): bool {
                return $mail->hasBcc('orders@eazywear.id');
            }
        );
    }

    public function test_other_customer_emails_do_not_have_orders_bcc(): void
    {
        $transaction = new Transaction([
            'invoice_number' => 'INV-BCC-OTHER',
        ]);

        $emptyCollection = new Collection();

        $transaction->setRelation('shipments', $emptyCollection);
        $transaction->setRelation('items', $emptyCollection);

        $mailables = [
            new OrderCreatedMail($transaction),
            new OrderProcessingMail($transaction),
            new OrderShippedMail($transaction),
            new OrderCompletedMail($transaction),
            new PaymentExpiredMail($transaction),
            new PickupCompletedMail($transaction),
            new PreOrderShippingScheduleMail($transaction, $emptyCollection),
            new PreOrderPickupCorrectionMail('Customer', []),
        ];

        foreach ($mailables as $mailable) {
            $this->assertFalse(
                $mailable->hasBcc('orders@eazywear.id'),
                class_basename($mailable)
                    . ' must not BCC orders@eazywear.id.'
            );

            $this->assertSame([], $mailable->envelope()->bcc);
        }
    }
}
