<?php

namespace App\Mail;

use App\Services\SystemEmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompletionStatusNotificationMail extends Mailable
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
        $defaultSubject = $this->payload['subject']
            ?? ('Status Update - ' . ($this->payload['service_name'] ?? 'Citizen Service'));

        $templateService = app(SystemEmailTemplateService::class);
        $template = $templateService->resolve('completion_status', $this->payload, $defaultSubject);

        if ($template['has_custom_body']) {
            return $this
                ->subject($template['subject'])
                ->view('emails.layouts.professional-system-template', [
                    'mailTitle' => 'Completion and Status Notification',
                    'mailSubtitle' => 'Gram Panchayat Citizen Services',
                    'bodyHtml' => $template['body_html'],
                    'ctaLink' => $this->payload['portal_link'] ?? null,
                    'ctaText' => !empty($this->payload['portal_link']) ? 'View Details' : null,
                ]);
        }

        return $this
            ->subject($template['subject'])
            ->view('emails.notifications.completion-status');
    }
}
