<?php

namespace App\Services;

use App\Mail\CompletionStatusNotificationMail;
use App\Mail\InvoiceGeneratedMail;
use App\Mail\PaymentConfirmationMail;
use App\Mail\PaymentDueReminderMail;
use App\Mail\PaymentStatusUpdateMail;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SystemEmailSimulationService
{
    /**
     * Simulate all system-generated emails for preview and optional delivery.
     */
    public function simulate(string $recipientEmail, string $recipientName = 'Soham Tare', bool $sendEmails = true): array
    {
        $timestamp = now();
        $runId = $timestamp->format('Ymd_His') . '_' . Str::lower(Str::random(6));
        $previewDirectory = 'mail-previews/' . $runId;

        Storage::disk('public')->makeDirectory($previewDirectory);

        $emailDefinitions = $this->buildEmailDefinitions($recipientName, $timestamp);
        $results = [];

        foreach ($emailDefinitions as $index => $definition) {
            /** @var Mailable $previewMailable */
            $previewMailable = $definition['factory']();
            $renderedHtml = $previewMailable->render();

            $fileName = sprintf('%02d-%s.html', $index + 1, $definition['slug']);
            $relativePreviewPath = $previewDirectory . '/' . $fileName;

            Storage::disk('public')->put($relativePreviewPath, $renderedHtml);

            $status = $sendEmails ? 'sent' : 'preview-only';
            $error = null;

            if ($sendEmails) {
                try {
                    /** @var Mailable $sendMailable */
                    $sendMailable = $definition['factory']();
                    Mail::to($recipientEmail)->send($sendMailable);
                } catch (\Throwable $throwable) {
                    $status = 'failed';
                    $error = $throwable->getMessage();

                    Log::error('System email simulation failed to send email.', [
                        'run_id' => $runId,
                        'email_type' => $definition['type'],
                        'recipient_email' => $recipientEmail,
                        'error' => $throwable->getMessage(),
                    ]);
                }
            }

            $results[] = [
                'type' => $definition['type'],
                'label' => $definition['label'],
                'subject' => $definition['subject'],
                'status' => $status,
                'error' => $error,
                'preview_file' => storage_path('app/public/' . $relativePreviewPath),
                'preview_url' => asset('storage/' . $relativePreviewPath),
            ];
        }

        return [
            'run_id' => $runId,
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'send_mode' => $sendEmails ? 'send-and-preview' : 'preview-only',
            'generated_at' => $timestamp->toDateTimeString(),
            'preview_directory' => storage_path('app/public/' . $previewDirectory),
            'emails' => $results,
            'summary' => [
                'total' => count($results),
                'sent' => collect($results)->where('status', 'sent')->count(),
                'failed' => collect($results)->where('status', 'failed')->count(),
                'preview_only' => collect($results)->where('status', 'preview-only')->count(),
            ],
        ];
    }

    /**
     * Build realistic, real-world email scenarios for system testing.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildEmailDefinitions(string $recipientName, Carbon $timestamp): array
    {
        $invoiceNumber = 'INV-' . $timestamp->format('Ymd') . '-0049';
        $transactionIdSuccess = 'TXN' . $timestamp->format('YmdHis') . 'A1';
        $transactionIdFailure = 'TXN' . $timestamp->format('YmdHis') . 'F1';
        $dueDate = $timestamp->copy()->addDays(7);
        $billingPeriod = $timestamp->format('F Y');

        return [
            [
                'type' => 'invoice_generation',
                'slug' => 'invoice-generation',
                'label' => 'Invoice generation email',
                'subject' => 'Invoice Generated - Water Tax - ' . $invoiceNumber,
                'factory' => fn (): Mailable => new InvoiceGeneratedMail([
                    'citizen_name' => $recipientName,
                    'customer_no' => 'NGP-W-10425',
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $timestamp,
                    'billing_period' => $billingPeriod,
                    'due_date' => $dueDate,
                    'tax_type' => 'water_tax',
                    'line_items' => [
                        [
                            'description' => 'Water tax charges for ' . $billingPeriod,
                            'amount' => 720.00,
                        ],
                        [
                            'description' => 'Previous balance adjustment',
                            'amount' => 180.00,
                        ],
                    ],
                    'subtotal' => 900.00,
                    'late_fee_note' => 'A late fee of 2% applies after the due date.',
                    'amount_due' => 900.00,
                    'payment_link' => url('/citizen/login'),
                    'support_email' => config('mail.from.address', 'support@gramportal.in'),
                ]),
            ],
            [
                'type' => 'due_date_reminder',
                'slug' => 'due-date-reminder',
                'label' => 'Due date reminder email',
                'subject' => 'Payment Reminder - Water Tax due on ' . $dueDate->format('d M Y'),
                'factory' => fn (): Mailable => new PaymentDueReminderMail([
                    'citizen_name' => $recipientName,
                    'tax_type' => 'water_tax',
                    'customer_no' => 'NGP-W-10425',
                    'bill_no' => 'WT-' . $timestamp->format('Ym') . '-10425',
                    'amount_due' => 900.00,
                    'due_date' => $dueDate->format('d M Y'),
                    'billing_period' => $billingPeriod,
                    'payment_link' => url('/citizen/login'),
                ]),
            ],
            [
                'type' => 'payment_status_success',
                'slug' => 'payment-status-success',
                'label' => 'Payment status update (success)',
                'subject' => 'Payment Confirmation - Water Tax - ' . $transactionIdSuccess,
                'factory' => fn (): Mailable => new PaymentConfirmationMail([
                    'citizen_name' => $recipientName,
                    'tax_type' => 'water_tax',
                    'transaction_id' => $transactionIdSuccess,
                    'payment_date' => $timestamp,
                    'invoice_number' => $invoiceNumber,
                    'billing_period' => $billingPeriod,
                    'due_date' => $dueDate,
                    'tax_amount' => 900.00,
                    'convenience_fee' => 18.00,
                    'total_amount' => 918.00,
                    'invoice_link' => url('/citizen/billing/10425/invoice'),
                ]),
            ],
            [
                'type' => 'payment_status_failure',
                'slug' => 'payment-status-failure',
                'label' => 'Payment status update (failure)',
                'subject' => 'Payment Failed - Water Tax - ' . $transactionIdFailure,
                'factory' => fn (): Mailable => new PaymentStatusUpdateMail([
                    'citizen_name' => $recipientName,
                    'tax_type' => 'water_tax',
                    'status' => 'failed',
                    'transaction_id' => $transactionIdFailure,
                    'attempted_at' => $timestamp,
                    'amount' => 918.00,
                    'failure_reason' => 'Transaction declined by issuing bank. Please retry or use another payment method.',
                    'next_step' => 'Retry payment from the portal or contact your bank if the issue continues.',
                    'retry_link' => url('/citizen/pay-bill'),
                ]),
            ],
            [
                'type' => 'completion_status_notification',
                'slug' => 'completion-status-notification',
                'label' => 'Completion/status notification email',
                'subject' => 'Status Update - Grievance Ticket GRV-' . $timestamp->format('Y') . '-0182 Resolved',
                'factory' => fn (): Mailable => new CompletionStatusNotificationMail([
                    'citizen_name' => $recipientName,
                    'ticket_number' => 'GRV-' . $timestamp->format('Y') . '-0182',
                    'service_name' => 'Grievance Redressal',
                    'previous_status' => 'In Progress',
                    'current_status' => 'Resolved',
                    'updated_at' => $timestamp,
                    'summary' => 'Streetlight complaint near Somaiya campus has been completed by the electrical maintenance team.',
                    'admin_remarks' => 'New LED fixture installed and night test confirmed successful.',
                    'portal_link' => url('/grievance/track'),
                ]),
            ],
        ];
    }
}
