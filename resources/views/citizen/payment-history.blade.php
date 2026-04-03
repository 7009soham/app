@extends('citizen.layout')

@section('title', 'Payment History')
@section('page-title', 'Payment History')

@push('styles')
<style>
    /* Page Header */
    .page-header-card {
        background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
        border-radius: var(--radius-lg);
        padding: 32px;
        margin-bottom: 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .page-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    }

    .header-content {
        position: relative;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,0.2);
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }

    .header-text h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .header-text p {
        opacity: 0.9;
        font-size: 15px;
    }

    @media (max-width: 480px) {
        .page-header-card {
            padding: 20px 16px;
            margin-bottom: 20px;
        }
        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }
        .header-text h2 { font-size: 18px; }
        .header-text p { font-size: 13px; }
    }

    /* Payments Card */
    .payments-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .payments-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }

    .payments-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* Payment Item */
    .payment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        transition: var(--transition);
        gap: 12px;
    }

    .payment-item:last-child {
        border-bottom: none;
    }

    .payment-item:hover {
        background: var(--surface-secondary);
    }

    .payment-left {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        flex: 1;
    }

    .payment-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .payment-icon.completed {
        background: #dcfce7;
        color: #16a34a;
    }

    .payment-icon.pending {
        background: #fef3c7;
        color: #d97706;
    }

    .payment-icon.failed {
        background: #fee2e2;
        color: #dc2626;
    }

    .payment-info {
        min-width: 0;
    }

    .payment-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .payment-info p {
        font-size: 13px;
        color: var(--text-secondary);
        word-break: break-all;
    }

    .payment-right {
        text-align: right;
        flex-shrink: 0;
    }

    @media (max-width: 480px) {
        .payment-item {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px;
            gap: 12px;
        }
        .payment-left { width: 100%; }
        .payment-right { width: 100%; display: flex; justify-content: space-between; align-items: center; }
        .payment-icon { flex-shrink: 0; }
        .payment-info p { word-break: break-all; }
    }

    .payment-amount {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .payment-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .payment-status.completed,
    .payment-status.success {
        background: #dcfce7;
        color: #16a34a;
    }

    .payment-status.pending {
        background: #fef3c7;
        color: #d97706;
    }

    .payment-status.failed {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 20px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: center;
    }

    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
    }

    .pagination li a,
    .pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: var(--transition);
    }

    .pagination li a {
        background: var(--surface-secondary);
        color: var(--text-primary);
    }

    .pagination li a:hover {
        background: var(--primary);
        color: white;
    }

    .pagination li.active span {
        background: var(--primary);
        color: white;
    }

    .pagination li.disabled span {
        background: var(--surface-secondary);
        color: var(--text-muted);
    }

    /* Empty State */
    .empty-state {
        padding: 64px 24px;
        text-align: center;
    }

    .empty-state i {
        font-size: 64px;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 15px;
        color: var(--text-secondary);
        max-width: 400px;
        margin: 0 auto;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header-card">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-history"></i>
        </div>
        <div class="header-text">
            <h2>{{ __('messages.payment_history') }}</h2>
            <p>{{ __('messages.view_payment_history') }}</p>
        </div>
    </div>
</div>

<!-- Payments List -->
<div class="payments-card">
    <div class="payments-header">
        <h3 class="payments-title">{{ __('messages.all_transactions') }}</h3>
    </div>

    @if($payments->count() > 0)
        @foreach($payments as $payment)
        <div class="payment-item">
            <div class="payment-left">
                <div class="payment-icon {{ in_array($payment->payment_status, ['completed', 'success']) ? 'completed' : $payment->payment_status }}">
                    @if(in_array($payment->payment_status, ['completed', 'success']))
                        <i class="fas fa-check-circle"></i>
                    @elseif($payment->payment_status === 'pending')
                        <i class="fas fa-clock"></i>
                    @else
                        <i class="fas fa-times-circle"></i>
                    @endif
                </div>
                <div class="payment-info">
                    <h4>{{ $payment->taxType->name ?? __('messages.tax_payment') }}</h4>
                    <p>
                        {{ $payment->transaction_id }} • 
                        {{ $payment->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>
            </div>
            <div class="payment-right">
                <div class="payment-amount">₹{{ number_format($payment->amount, 2) }}</div>
                @php
                    $statusClass = in_array($payment->payment_status, ['completed', 'success']) ? 'completed' : $payment->payment_status;
                    $statusText = in_array($payment->payment_status, ['completed', 'success']) ? 'Completed' : ucfirst($payment->payment_status);
                @endphp
                <span class="payment-status {{ $statusClass }}">
                    {{ $statusText }}
                </span>
            </div>
        </div>
        @endforeach

        @if($payments->hasPages())
        <div class="pagination-wrapper">
            {{ $payments->links() }}
        </div>
        @endif
    @else
    <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>{{ __('messages.no_payment_history') }}</h3>
        <p>{{ __('messages.no_payment_history_desc') }}</p>
    </div>
    @endif
</div>
@endsection
