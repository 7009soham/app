@extends('citizen.layout')

@section('title', 'Transaction History')
@section('page-title', 'My Transactions')

@push('styles')
<style>
    .section-card {
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .section-header {
        padding: 24px;
        border-bottom: 1px solid var(--border);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        color: var(--text-primary);
    }

    .section-title i {
        color: var(--primary);
    }

    /* Desktop Table View */
    .desktop-table {
        display: block;
    }

    .mobile-cards {
        display: none;
    }

    .table-responsive {
        overflow-x: auto;
        padding: 0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: var(--surface-secondary);
    }

    .table th {
        padding: 16px 24px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .table td {
        padding: 16px 24px;
        font-size: 14px;
        color: var(--text-primary);
    }

    .table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }

    .table tbody tr:hover {
        background: var(--surface-secondary);
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-primary {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .failure-reason {
        color: #b91c1c;
        font-size: 12px;
        line-height: 1.4;
        max-width: 280px;
    }

    /* Mobile Card View */
    @media (max-width: 768px) {
        .desktop-table {
            display: none;
        }

        .mobile-cards {
            display: block;
            padding: 16px;
        }

        .transaction-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }

        .transaction-card:hover {
            box-shadow: var(--shadow);
        }

        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 12px;
        }

        .transaction-id {
            font-family: monospace;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
        }

        .transaction-amount {
            font-size: 20px;
            font-weight: 700;
            color: #16a34a;
            white-space: nowrap;
        }

        .transaction-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .transaction-row:last-child {
            border-bottom: none;
        }

        .transaction-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
        }

        .transaction-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            text-align: right;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
            color: var(--text-secondary);
        }

        .empty-text {
            color: var(--text-secondary);
            font-size: 14px;
        }
    }

    .pagination {
        padding: 24px;
        border-top: 1px solid var(--border);
    }

    @media (max-width: 640px) {
        .section-header {
            padding: 16px;
        }

        .section-title {
            font-size: 18px;
        }

        .mobile-cards {
            padding: 12px;
        }

        .transaction-card {
            padding: 16px;
        }
    }
</style>
@endpush

@section('content')
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-history"></i>
            {{ app()->getLocale() == 'hi' ? 'हाल के लेनदेन' : (app()->getLocale() == 'mr' ? 'अलीकडील व्यवहार' : 'Recent Transactions') }}
        </h3>
    </div>

    <!-- Desktop Table View -->
    <div class="desktop-table">
        <div class="section-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Tax Type</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Failure Reason</th>
                            <th>Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $rawStatus = strtolower($payment->status ?? $payment->payment_status ?? 'pending');
                                $statusKey = in_array($rawStatus, ['success', 'completed'])
                                    ? 'success'
                                    : ($rawStatus === 'failed' ? 'failed' : 'pending');
                                $statusBadgeClass = $statusKey === 'success' ? 'badge-success' : ($statusKey === 'failed' ? 'badge-danger' : 'badge-warning');
                                $statusLabel = strtoupper($statusKey);
                                $displayDate = \Carbon\Carbon::parse($payment->paid_at ?? $payment->created_at);
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-size: 13px;">{{ $payment->transaction_id }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $payment->tax_type == 'water_tax' ? 'badge-info' : 'badge-primary' }}">
                                        {{ $payment->taxType?->name ?? ($payment->tax_type == 'water_tax' ? 'Water Tax' : 'Property Tax') }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->period_start && $payment->period_end)
                                        {{ \Carbon\Carbon::parse($payment->period_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($payment->period_end)->format('d M Y') }}
                                    @else
                                        <span style="color: var(--text-muted);">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: #16a34a;">₹{{ number_format($payment->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    @if($statusKey === 'failed' && $payment->failure_reason)
                                        <div class="failure-reason" title="{{ $payment->failure_reason }}">{{ $payment->failure_reason }}</div>
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="text-transform: capitalize; font-size: 13px;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    {{ $displayDate->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 48px 24px; text-align: center; color: var(--text-secondary);">
                                    <i class="fas fa-receipt" style="font-size: 40px; margin-bottom: 16px; display: block; opacity: 0.3;"></i>
                                    <p>{{ app()->getLocale() == 'hi' ? 'आपने अभी तक कोई भुगतान नहीं किया है।' : (app()->getLocale() == 'mr' ? 'तुम्ही अद्याप कोणतीही देयके केली नाही.' : 'You haven\'t made any payments yet.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="mobile-cards">
        @forelse($payments as $payment)
            @php
                $rawStatus = strtolower($payment->status ?? $payment->payment_status ?? 'pending');
                $statusKey = in_array($rawStatus, ['success', 'completed'])
                    ? 'success'
                    : ($rawStatus === 'failed' ? 'failed' : 'pending');
                $statusBadgeClass = $statusKey === 'success' ? 'badge-success' : ($statusKey === 'failed' ? 'badge-danger' : 'badge-warning');
                $statusLabel = strtoupper($statusKey);
                $displayDate = \Carbon\Carbon::parse($payment->paid_at ?? $payment->created_at);
            @endphp
            <div class="transaction-card">
                <div class="transaction-header">
                    <div class="transaction-id">#{{ $payment->transaction_id }}</div>
                    <div class="transaction-amount">₹{{ number_format($payment->amount, 2) }}</div>
                </div>

                <div class="transaction-row">
                    <div class="transaction-label">
                        {{ app()->getLocale() == 'hi' ? 'कर प्रकार' : (app()->getLocale() == 'mr' ? 'कर प्रकार' : 'Tax Type') }}
                    </div>
                    <div class="transaction-value">
                        <span class="badge {{ $payment->tax_type == 'water_tax' ? 'badge-info' : 'badge-primary' }}">
                            {{ $payment->taxType?->name ?? ($payment->tax_type == 'water_tax' ? 'Water Tax' : 'Property Tax') }}
                        </span>
                    </div>
                </div>

                <div class="transaction-row">
                    <div class="transaction-label">
                        {{ app()->getLocale() == 'hi' ? 'अवधि' : (app()->getLocale() == 'mr' ? 'कालावधी' : 'Period') }}
                    </div>
                    <div class="transaction-value">
                        @if($payment->period_start && $payment->period_end)
                            {{ \Carbon\Carbon::parse($payment->period_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($payment->period_end)->format('d M Y') }}
                        @else
                            <span style="color: var(--text-muted);">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="transaction-row">
                    <div class="transaction-label">
                        {{ app()->getLocale() == 'hi' ? 'स्थिति' : (app()->getLocale() == 'mr' ? 'स्थिती' : 'Status') }}
                    </div>
                    <div class="transaction-value">
                        <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                @if($statusKey === 'failed' && $payment->failure_reason)
                    <div class="transaction-row">
                        <div class="transaction-label">
                            {{ app()->getLocale() == 'hi' ? 'विफलता कारण' : (app()->getLocale() == 'mr' ? 'अपयशाचे कारण' : 'Failure Reason') }}
                        </div>
                        <div class="transaction-value failure-reason">
                            {{ $payment->failure_reason }}
                        </div>
                    </div>
                @endif

                <div class="transaction-row">
                    <div class="transaction-label">
                        {{ app()->getLocale() == 'hi' ? 'भुगतान विधि' : (app()->getLocale() == 'mr' ? 'पेमेंट पद्धत' : 'Payment Method') }}
                    </div>
                    <div class="transaction-value" style="text-transform: capitalize;">
                        {{ str_replace('_', ' ', $payment->payment_method) }}
                    </div>
                </div>

                <div class="transaction-row">
                    <div class="transaction-label">
                        {{ app()->getLocale() == 'hi' ? 'तारीख' : (app()->getLocale() == 'mr' ? 'तारीख' : 'Date') }}
                    </div>
                    <div class="transaction-value">
                        {{ $displayDate->format('d M Y') }}<br>
                        <small style="color: var(--text-secondary);">{{ $displayDate->format('h:i A') }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <p class="empty-text">
                    {{ app()->getLocale() == 'hi' ? 'आपने अभी तक कोई भुगतान नहीं किया है।' : (app()->getLocale() == 'mr' ? 'तुम्ही अद्याप कोणतीही देयके केली नाही.' : 'You haven\'t made any payments yet.') }}
                </p>
            </div>
        @endforelse
    </div>

    @if($payments->hasPages())
        <div class="pagination">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
