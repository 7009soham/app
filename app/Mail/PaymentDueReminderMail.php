<?php

namespace App\Mail;

use App\Services\SystemEmailTemplateService;
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
        $defaultSubject = 'Payment Reminder - ' . $taxTypeLabel . ' due on ' . $dueDate;

        $templateService = app(SystemEmailTemplateService::class);
        $template = $templateService->resolve('due_date_reminder', $this->payload, $defaultSubject);

        if ($template['has_custom_body']) {
            return $this
                ->subject($template['subject'])
                ->view('emails.layouts.professional-system-template', [
                    'mailTitle' => 'Payment Due Reminder',
                    'mailSubtitle' => 'Gram Panchayat Tax Payment System',
                    'bodyHtml' => $template['body_html'],
                    'ctaLink' => $this->payload['payment_link'] ?? null,
                    'ctaText' => !empty($this->payload['payment_link']) ? 'Pay Now' : null,
                ]);
        }

        return $this
            ->subject($template['subject'])
            ->view('emails.payments.due-reminder');
    }
}
