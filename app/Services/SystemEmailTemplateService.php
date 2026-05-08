<?php

namespace App\Services;

use App\Models\SiteSetting;
use Carbon\Carbon;

class SystemEmailTemplateService
{
    /**
     * Settings keys for each customizable system email type.
     *
     * @var array<string, array{mode:string, subject:string, body:string}>
     */
    private array $templateKeyMap = [
        'invoice_generation' => [
            'mode' => 'email_template_invoice_mode',
            'subject' => 'email_template_invoice_subject',
            'body' => 'email_template_invoice_body',
        ],
        'due_date_reminder' => [
            'mode' => 'email_template_due_reminder_mode',
            'subject' => 'email_template_due_reminder_subject',
            'body' => 'email_template_due_reminder_body',
        ],
        'payment_success' => [
            'mode' => 'email_template_payment_success_mode',
            'subject' => 'email_template_payment_success_subject',
            'body' => 'email_template_payment_success_body',
        ],
        'payment_failure' => [
            'mode' => 'email_template_payment_failure_mode',
            'subject' => 'email_template_payment_failure_subject',
            'body' => 'email_template_payment_failure_body',
        ],
        'completion_status' => [
            'mode' => 'email_template_completion_mode',
            'subject' => 'email_template_completion_subject',
            'body' => 'email_template_completion_body',
        ],
    ];

    /**
     * Resolve customized subject/body from settings with placeholder injection.
     *
     * @return array{subject:string, body_html:?string, has_custom_body:bool}
     */
    public function resolve(string $templateType, array $payload, string $defaultSubject): array
    {
        $keyMap = $this->templateKeyMap[$templateType] ?? null;
        if ($keyMap === null) {
            return [
                'subject' => $defaultSubject,
                'body_html' => null,
                'has_custom_body' => false,
            ];
        }

        $subjectTemplate = trim((string) SiteSetting::get($keyMap['subject'], ''));
        $bodyTemplate = trim((string) SiteSetting::get($keyMap['body'], ''));
        $templateMode = strtolower(trim((string) SiteSetting::get($keyMap['mode'], '')));

        if (!in_array($templateMode, ['current', 'custom'], true)) {
            // Keep backward compatibility for older data where mode key does not exist yet.
            $templateMode = $bodyTemplate === '' ? 'current' : 'custom';
        }

        if ($templateMode === 'current') {
            return [
                'subject' => $defaultSubject,
                'body_html' => null,
                'has_custom_body' => false,
            ];
        }

        $tokenMap = $this->buildTokenMap($payload);

        $resolvedSubject = $defaultSubject;
        if ($subjectTemplate !== '') {
            $resolvedSubject = trim(strip_tags($this->applyTokens($subjectTemplate, $tokenMap)));
            if ($resolvedSubject === '') {
                $resolvedSubject = $defaultSubject;
            }
        }

        $resolvedBody = null;
        if ($bodyTemplate !== '') {
            $resolvedBody = $this->sanitizeHtml($this->applyTokens($bodyTemplate, $tokenMap));
            if (trim(strip_tags($resolvedBody)) === '') {
                $resolvedBody = null;
            }
        }

        return [
            'subject' => $resolvedSubject,
            'body_html' => $resolvedBody,
            'has_custom_body' => $resolvedBody !== null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildTokenMap(array $payload): array
    {
        $citizenName = $this->pick($payload, ['citizen_name', 'user_name', 'name'], 'Citizen');
        $taxType = strtolower((string) ($payload['tax_type'] ?? ''));
        $taxTypeLabel = $taxType === 'water_tax' ? 'Water Tax' : ($taxType === 'property_tax' ? 'Property Tax' : 'Tax');

        $status = $this->pick($payload, ['status', 'current_status'], 'Updated');
        $status = ucwords(str_replace('_', ' ', strtolower($status)));

        $tokenMap = [
            'user_name' => $citizenName,
            'citizen_name' => $citizenName,
            'tax_type' => $taxType,
            'tax_type_label' => $taxTypeLabel,
            'customer_no' => $this->pick($payload, ['customer_no'], '-'),
            'bill_no' => $this->pick($payload, ['bill_no'], '-'),
            'invoice_number' => $this->pick($payload, ['invoice_number'], '-'),
            'invoice_date' => $this->asDateTime($payload['invoice_date'] ?? null),
            'billing_period' => $this->pick($payload, ['billing_period'], '-'),
            'due_date' => $this->asDate($payload['due_date'] ?? null),
            'payment_date' => $this->asDateTime($payload['payment_date'] ?? null),
            'attempted_at' => $this->asDateTime($payload['attempted_at'] ?? null),
            'updated_at' => $this->asDateTime($payload['updated_at'] ?? null),
            'transaction_id' => $this->pick($payload, ['transaction_id'], '-'),
            'status' => $status,
            'previous_status' => $this->pick($payload, ['previous_status'], '-'),
            'current_status' => $this->pick($payload, ['current_status'], '-'),
            'ticket_number' => $this->pick($payload, ['ticket_number'], '-'),
            'service_name' => $this->pick($payload, ['service_name'], 'Citizen Service'),
            'summary' => $this->pick($payload, ['summary'], '-'),
            'admin_remarks' => $this->pick($payload, ['admin_remarks'], '-'),
            'failure_reason' => $this->pick($payload, ['failure_reason'], '-'),
            'next_step' => $this->pick($payload, ['next_step'], '-'),
            'amount_due' => $this->asCurrency($payload['amount_due'] ?? ($payload['total_amount'] ?? null)),
            'tax_amount' => $this->asCurrency($payload['tax_amount'] ?? null),
            'convenience_fee' => $this->asCurrency($payload['convenience_fee'] ?? null),
            'total_amount' => $this->asCurrency($payload['total_amount'] ?? ($payload['amount'] ?? null)),
            'details_table' => $this->buildDetailsTable([
                'User Name' => $citizenName,
                'Tax Type' => $taxTypeLabel,
                'Invoice Number' => $this->pick($payload, ['invoice_number'], '-'),
                'Billing Period' => $this->pick($payload, ['billing_period'], '-'),
                'Due Date' => $this->asDate($payload['due_date'] ?? null),
                'Transaction ID' => $this->pick($payload, ['transaction_id'], '-'),
                'Amount Due' => $this->asCurrency($payload['amount_due'] ?? null),
                'Total Amount' => $this->asCurrency($payload['total_amount'] ?? ($payload['amount'] ?? null)),
                'Status' => $status,
                'Ticket Number' => $this->pick($payload, ['ticket_number'], '-'),
                'Current Status' => $this->pick($payload, ['current_status'], '-'),
            ]),
            'invoice_line_items' => $this->buildInvoiceItemsTable($payload['line_items'] ?? []),
        ];

        return $tokenMap;
    }

    /**
     * @param array<string, string> $tokenMap
     */
    private function applyTokens(string $template, array $tokenMap): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function (array $matches) use ($tokenMap) {
            return $tokenMap[$matches[1]] ?? '';
        }, $template) ?? $template;
    }

    private function asDate($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function asDateTime($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('d M Y, h:i A');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function asCurrency($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return 'Rs ' . number_format((float) $value, 2);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $keys
     */
    private function pick(array $payload, array $keys, string $default): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            if ($value === null || $value === '') {
                continue;
            }

            return (string) $value;
        }

        return $default;
    }

    /**
     * @param array<string, string> $rows
     */
    private function buildDetailsTable(array $rows): string
    {
        $filtered = array_filter($rows, static fn ($value) => $value !== '-' && $value !== '');
        if (empty($filtered)) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse;margin:12px 0;">';
        foreach ($filtered as $label => $value) {
            $html .= '<tr>';
            $html .= '<td style="padding:8px;border:1px solid #e2e8f0;background:#f8fafc;width:38%;">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * @param mixed $lineItems
     */
    private function buildInvoiceItemsTable($lineItems): string
    {
        if (!is_array($lineItems) || empty($lineItems)) {
            return '';
        }

        $html = '<table style="width:100%;border-collapse:collapse;margin:12px 0;">';
        $html .= '<thead><tr>';
        $html .= '<th style="text-align:left;padding:8px;border:1px solid #e2e8f0;background:#f8fafc;">Description</th>';
        $html .= '<th style="text-align:right;padding:8px;border:1px solid #e2e8f0;background:#f8fafc;">Amount</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($lineItems as $lineItem) {
            if (!is_array($lineItem)) {
                continue;
            }

            $description = (string) ($lineItem['description'] ?? '-');
            $amount = 'Rs ' . number_format((float) ($lineItem['amount'] ?? 0), 2);

            $html .= '<tr>';
            $html .= '<td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function sanitizeHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html) ?? $html;
        $html = preg_replace('/<(iframe|object|embed|form|input|button|textarea|select)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = preg_replace('/<(iframe|object|embed|form|input|button|textarea|select)[^>]*\/?>/is', '', $html) ?? $html;

        $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote><hr><div><span><table><thead><tbody><tr><th><td>';
        $html = strip_tags($html, $allowedTags);

        $html = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*\'[^\']*\'/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return $html;
    }
}
