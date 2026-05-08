<?php

namespace App\Mail;

use App\Services\SystemEmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdateMail extends Mailable
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
        $status = strtolower((string) ($this->payload['status'] ?? 'success'));

        $statusLabel = $status === 'failed' ? 'Payment Failed' : 'Payment Successful';
        $defaultSubject = $statusLabel . ' - ' . $taxTypeLabel . ' - ' . ($this->payload['transaction_id'] ?? '');

        $templateType = $status === 'failed' ? 'payment_failure' : 'payment_success';
        $templateService = app(SystemEmailTemplateService::class);
        $template = $templateService->resolve($templateType, $this->payload, $defaultSubject);

        if ($template['has_custom_body']) {
            return $this
                ->subject($template['subject'])
                ->view('emails.layouts.professional-system-template', [
                    'mailTitle' => $statusLabel,
                    'mailSubtitle' => 'Gram Panchayat Tax Payment System',
                    'bodyHtml' => $template['body_html'],
                    'ctaLink' => $status === 'failed'
                        ? ($this->payload['retry_link'] ?? null)
                        : ($this->payload['invoice_link'] ?? null),
                    'ctaText' => $status === 'failed' ? 'Retry Payment' : 'View Invoice',
                ]);
        }

        return $this
            ->subject($template['subject'])
            ->view('emails.payments.status-update');
    }
}
