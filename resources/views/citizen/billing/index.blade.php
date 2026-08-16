@extends('citizen.layout')

@section('title', 'Billing & Invoices')
@section('page-title', __('messages.billing_history'))

@php
    // The controller normalises water monthly bills and property annual bills
    // into one shape, so the two taxes cannot drift apart in the markup again.
    $statusStyles = [
        'paid'    => ['bg' => '#dcfce7', 'fg' => '#166534'],
        'partial' => ['bg' => '#fef3c7', 'fg' => '#92400e'],
        'overdue' => ['bg' => '#fee2e2', 'fg' => '#b91c1c'],
        'pending' => ['bg' => '#e2e8f0', 'fg' => '#334155'],
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card billing-card">
            <div class="card-header">
                <h3>{{ __('messages.my_bills') }}</h3>
            </div>

            <div class="card-body" style="padding: 0;">
                @if($bills->isEmpty())
                    <div class="billing-empty">
                        <i class="fas fa-file-invoice" aria-hidden="true"></i>
                        <p>{{ __('messages.no_bills_yet') }}</p>
                    </div>
                @else
                    <div class="billing-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('messages.bill_no') }}</th>
                                    <th>{{ __('messages.tax_type') }}</th>
                                    <th>{{ __('messages.period') }}</th>
                                    <th>{{ __('messages.amount') }}</th>
                                    <th>{{ __('messages.outstanding') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bills as $bill)
                                    @php $s = $statusStyles[$bill['status']] ?? $statusStyles['pending']; @endphp
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500;">#{{ $bill['bill_no'] }}</div>
                                            @if($bill['paid_date'])
                                                <div class="billing-sub">{{ \Carbon\Carbon::parse($bill['paid_date'])->translatedFormat('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $bill['tax'] }}</td>
                                        <td>{{ $bill['period'] }}</td>
                                        <td style="font-weight: 600;">₹{{ number_format($bill['amount'], 2) }}</td>
                                        <td style="font-weight: 600;">₹{{ number_format($bill['balance'], 2) }}</td>
                                        <td>
                                            <span class="billing-pill" style="background: {{ $s['bg'] }}; color: {{ $s['fg'] }};">
                                                {{ __('messages.' . $bill['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ $bill['invoice_url'] }}" class="btn-invoice" target="_blank" rel="noopener">
                                                <i class="fas fa-print" aria-hidden="true"></i> {{ __('messages.view_invoice') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="billing-mobile-cards">
                        @foreach($bills as $bill)
                            @php $s = $statusStyles[$bill['status']] ?? $statusStyles['pending']; @endphp
                            <div class="billing-mobile-card">
                                <div class="bmc-head">
                                    <span style="font-weight:700;">#{{ $bill['bill_no'] }}</span>
                                    <span class="billing-pill" style="background: {{ $s['bg'] }}; color: {{ $s['fg'] }};">
                                        {{ __('messages.' . $bill['status']) }}
                                    </span>
                                </div>
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.tax_type') }}</span>
                                    <span class="bmc-value">{{ $bill['tax'] }}</span>
                                </div>
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.period') }}</span>
                                    <span class="bmc-value">{{ $bill['period'] }}</span>
                                </div>
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.amount') }}</span>
                                    <span class="bmc-value" style="font-weight:600;">₹{{ number_format($bill['amount'], 2) }}</span>
                                </div>
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.outstanding') }}</span>
                                    <span class="bmc-value" style="font-weight:600;">₹{{ number_format($bill['balance'], 2) }}</span>
                                </div>
                                <div style="margin-top:12px;">
                                    <a href="{{ $bill['invoice_url'] }}" class="btn-invoice" target="_blank" rel="noopener" style="width:100%; justify-content:center;">
                                        <i class="fas fa-print" aria-hidden="true"></i> {{ __('messages.view_invoice') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Bills are generated by the office by hand, so a citizen can have
             real successful payments and still have no bill row. Showing only an
             empty table in that case reads as "my payment vanished", which is
             the complaint that surfaced this whole defect. --}}
        @if($payments->isNotEmpty())
            <div class="card billing-card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>{{ __('messages.payments_received') }}</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="billing-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('messages.transaction_id') }}</th>
                                    <th>{{ __('messages.tax_type') }}</th>
                                    <th>{{ __('messages.amount') }}</th>
                                    <th>{{ __('messages.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td style="font-family: ui-monospace, monospace; font-size: 12px;">{{ $payment->transaction_id }}</td>
                                        <td>{{ $payment->tax_type === 'water_tax' ? __('messages.water_tax') : __('messages.property_tax') }}</td>
                                        <td style="font-weight: 600;">₹{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->paid_at ? $payment->paid_at->translatedFormat('d M Y, g:i A') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="billing-mobile-cards">
                        @foreach($payments as $payment)
                            <div class="billing-mobile-card">
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.transaction_id') }}</span>
                                    <span class="bmc-value" style="font-family: ui-monospace, monospace; font-size:11px;">{{ $payment->transaction_id }}</span>
                                </div>
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.amount') }}</span>
                                    <span class="bmc-value" style="font-weight:600;">₹{{ number_format($payment->amount, 2) }}</span>
                                </div>
                                <div class="bmc-row">
                                    <span class="bmc-label">{{ __('messages.date') }}</span>
                                    <span class="bmc-value">{{ $payment->paid_at ? $payment->paid_at->translatedFormat('d M Y') : '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .billing-card {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
    }

    .billing-card .card-header {
        padding: 20px;
        border-bottom: 1px solid var(--border);
    }

    .billing-card .card-header h3 {
        margin: 0;
        font-size: 18px;
        color: var(--text-primary);
    }

    .billing-empty {
        padding: 40px;
        text-align: center;
        color: var(--text-secondary);
    }

    .billing-empty i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.2;
        display: block;
    }

    .billing-table-wrap table {
        width: 100%;
        border-collapse: collapse;
    }

    .billing-table-wrap thead tr {
        background: var(--surface-secondary);
        text-align: left;
    }

    .billing-table-wrap th {
        padding: 12px 20px;
        font-weight: 600;
        font-size: 13px;
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .billing-table-wrap td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }

    .billing-sub {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .billing-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-invoice {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: var(--surface-secondary);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-invoice:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: var(--primary);
    }

    /* The table scrolls inside its own box so the page never scrolls sideways. */
    .billing-table-wrap { display: block; overflow-x: auto; }
    .billing-mobile-cards { display: none; }

    @media (max-width: 720px) {
        .billing-table-wrap { display: none; }
        .billing-mobile-cards { display: block; padding: 12px; }

        .billing-mobile-card {
            background: var(--surface-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .bmc-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding-bottom: 8px;
            margin-bottom: 4px;
            border-bottom: 1px solid var(--border);
        }

        .bmc-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 6px 0;
            border-bottom: 1px solid var(--border);
            gap: 8px;
        }

        .bmc-row:last-of-type { border-bottom: none; }

        .bmc-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .bmc-value {
            font-size: 13px;
            color: var(--text-primary);
            text-align: right;
            min-width: 0;
            overflow-wrap: anywhere;
        }
    }
</style>
@endsection
