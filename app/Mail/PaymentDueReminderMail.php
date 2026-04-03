<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    /**
     * Create a new message instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $taxType = $this->payload['tax_type'] ?? 'tax';
        $taxTypeLabel = $taxType === 'water_tax' ? 'Water Tax' : 'Property Tax';
        $dueDate = $this->payload['due_date'] ?? null;

        return $this
            ->subject('Payment Reminder - ' . $taxTypeLabel . ' due on ' . $dueDate)
            ->view('emails.payments.due-reminder');
    }
}
