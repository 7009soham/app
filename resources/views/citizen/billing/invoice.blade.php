<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Tax Invoice {{ $bill->bill_no ? '- ' . $bill->bill_no : '' }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --brand: #1e3a5f;
            --accent: #f97316;
            --bg: #f8fafc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: var(--bg);
            font-family: Arial, sans-serif;
            color: var(--ink);
        }

        .invoice {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        .header {
            background: linear-gradient(135deg, var(--brand), #0f2740);
            color: #ffffff;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
        }

        .title h1 {
            margin: 0;
            font-size: 24px;
        }

        .title p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }

        .tag {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .body {
            padding: 24px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 14px;
            background: #ffffff;
        }

        .label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .value {
            font-size: 15px;
            font-weight: 600;
            word-break: break-word;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid var(--line);
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            color: #334155;
        }

        .right { text-align: right; }

        .total-row {
            background: #fff7ed;
            font-weight: 700;
        }

        .note {
            margin-top: 16px;
            padding: 12px;
            border-left: 4px solid var(--accent);
            background: #fff7ed;
            color: #7c2d12;
            font-size: 13px;
        }

        .footer {
            margin-top: 26px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--muted);
            gap: 20px;
        }

        .print-btn {
            position: fixed;
            right: 20px;
            top: 20px;
            border: none;
            background: var(--brand);
            color: #fff;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .invoice { box-shadow: none; border: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
@php
    $previousOutstanding = (float) ($bill->balance ?? 0);
    $currentBill = (float) ($bill->monthly_bill ?? 0);
    $otherCharges = (float) ($bill->oversize_charge ?? 0);
    $grossAmount = $previousOutstanding + $currentBill + $otherCharges;
    $paidAmount = (float) ($bill->amount_paid ?? 0);
    $netOutstanding = max(0, $grossAmount - $paidAmount);
    $invoiceDate = $bill->payment_date ? $bill->payment_date->format('d M Y') : ($bill->updated_at ? $bill->updated_at->format('d M Y') : now()->format('d M Y'));
    $latestTaxPayment = $latestPayment ?? null;
    $latestPaymentData = is_array($latestTaxPayment?->payment_data ?? null) ? $latestTaxPayment->payment_data : [];
    $hasReceipt = !empty($latestTaxPayment);
    $receiptStatus = $hasReceipt ? 'PAID' : 'PENDING';
    $lastTransactionTaxAmount = (float) ($latestPaymentData['tax_amount'] ?? ($latestTaxPayment?->amount ?? 0));
    $lastTransactionFee = (float) ($latestPaymentData['convenience_fee'] ?? 0);
    $lastPaymentDate = $latestTaxPayment?->paid_at ? \Carbon\Carbon::parse($latestTaxPayment->paid_at)->format('d M Y, h:i A') : null;
@endphp

<button onclick="window.print()" class="print-btn">Print Invoice</button>

<div class="invoice">
    <div class="header">
        <div class="title">
            <h1>Water Tax Invoice</h1>
            <p>Gram Panchayat Tax Payment System</p>
        </div>
        <div class="tag">{{ $hasReceipt ? 'PAYMENT RECEIPT' : 'OFFICIAL COPY' }}</div>
    </div>

    <div class="body">
        <div class="grid">
            <div class="card">
                <div class="label">Invoice Number</div>
                <div class="value">{{ $bill->bill_no ?: $bill->customer_no . '-' . ($bill->id ?? '') }}</div>
            </div>
            <div class="card">
                <div class="label">Invoice Date</div>
                <div class="value">{{ $invoiceDate }}</div>
            </div>
            <div class="card">
                <div class="label">Invoice Type</div>
                <div class="value">{{ $hasReceipt ? 'Post-Payment Invoice' : 'Outstanding Invoice' }}</div>
            </div>
            <div class="card">
                <div class="label">Customer Name</div>
                <div class="value">{{ $citizen->name }}</div>
            </div>
            <div class="card">
                <div class="label">Customer Number</div>
                <div class="value">{{ $bill->customer_no ?? $citizen->customer_no ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Billing Period</div>
                <div class="value">{{ $bill->period ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Receipt Number</div>
                <div class="value">{{ $bill->receipt_no ?? '-' }}</div>
            </div>
            <div class="card">
                <div class="label">Payment Status</div>
                <div class="value">{{ $receiptStatus }}</div>
            </div>
            @if($hasReceipt)
            <div class="card">
                <div class="label">Last Transaction ID</div>
                <div class="value">{{ $latestTaxPayment->transaction_id ?? '-' }}</div>
            </div>
            @endif
            @if($lastPaymentDate)
            <div class="card">
                <div class="label">Last Payment Date</div>
                <div class="value">{{ $lastPaymentDate }}</div>
            </div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th class="right">Amount (Rs)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Previous Outstanding</td>
                    <td class="right">{{ number_format($previousOutstanding, 2) }}</td>
                </tr>
                <tr>
                    <td>Current Water Charges</td>
                    <td class="right">{{ number_format($currentBill, 2) }}</td>
                </tr>
                <tr>
                    <td>Other Charges</td>
                    <td class="right">{{ number_format($otherCharges, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Gross Amount</td>
                    <td class="right">{{ number_format($grossAmount, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td class="right">{{ number_format($paidAmount, 2) }}</td>
                </tr>
                @if($hasReceipt)
                <tr>
                    <td>Last Transaction Tax Amount</td>
                    <td class="right">{{ number_format($lastTransactionTaxAmount, 2) }}</td>
                </tr>
                <tr>
                    <td>Last Transaction Convenience Fee</td>
                    <td class="right">{{ number_format($lastTransactionFee, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Net Outstanding</td>
                    <td class="right">{{ number_format($netOutstanding, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            @if($hasReceipt)
            This invoice has been updated after payment. Please keep this copy for your records.
            @else
            Please make payment before the due date to avoid penalty. Keep this invoice for your records.
            @endif
        </div>

        <div class="footer">
            <div>
                <strong>Citizen Address:</strong><br>
                {{ $citizen->address ?? '-' }}
            </div>
            <div>
                <strong>Authorized Signatory</strong><br>
                Gram Panchayat Office
            </div>
        </div>
    </div>
</div>
</body>
</html>
