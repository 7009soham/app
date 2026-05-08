<?php

namespace App\Mail;

use App\Services\SystemEmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceGeneratedMail extends Mailable
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
        $defaultSubject = 'Invoice Generated - ' . $taxTypeLabel . ' - ' . ($this->payload['invoice_number'] ?? '');

        $templateService = app(SystemEmailTemplateService::class);
        $template = $templateService->resolve('invoice_generation', $this->payload, $defaultSubject);

        if ($template['has_custom_body']) {
            return $this
                ->subject($template['subject'])
                ->view('emails.layouts.professional-system-template', [
                    'mailTitle' => 'Invoice Generated',
                    'mailSubtitle' => 'Gram Panchayat Tax & Citizen Services',
                    'bodyHtml' => $template['body_html'],
                    'ctaLink' => $this->payload['payment_link'] ?? null,
                    'ctaText' => !empty($this->payload['payment_link']) ? 'Pay Invoice' : null,
                ]);
        }

        return $this
            ->subject($template['subject'])
            ->view('emails.payments.invoice-generated');
    }
}
