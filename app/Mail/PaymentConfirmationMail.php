<?php

namespace App\Mail;

use App\Services\SystemEmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
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
        $defaultSubject = 'Payment Confirmation - ' . $taxTypeLabel . ' - ' . ($this->payload['transaction_id'] ?? '');

        $templateService = app(SystemEmailTemplateService::class);
        $template = $templateService->resolve('payment_success', $this->payload, $defaultSubject);

        if ($template['has_custom_body']) {
            return $this
                ->subject($template['subject'])
                ->view('emails.layouts.professional-system-template', [
                    'mailTitle' => 'Payment Confirmation',
                    'mailSubtitle' => 'Gram Panchayat Tax Payment System',
                    'bodyHtml' => $template['body_html'],
                    'ctaLink' => $this->payload['invoice_link'] ?? null,
                    'ctaText' => !empty($this->payload['invoice_link']) ? 'View Invoice' : null,
                ]);
        }

        return $this
            ->subject($template['subject'])
            ->view('emails.payments.confirmation');
    }
}
