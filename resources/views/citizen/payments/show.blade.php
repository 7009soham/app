@extends('citizen.layout')

@section('title', 'Transaction Receipt')
@section('page-title', 'Transaction Details')

@section('content')
@php
    $rawStatus = strtolower($payment->status ?? $payment->payment_status ?? 'pending');
    $statusKey = in_array($rawStatus, ['success', 'completed']) ? 'success' : ($rawStatus === 'failed' ? 'failed' : 'pending');
    $statusLabel = strtoupper($statusKey);
    $displayDate = \Carbon\Carbon::parse($payment->paid_at ?? $payment->created_at);
    $statusColor = $statusKey === 'success' ? '#16a34a' : ($statusKey === 'failed' ? '#dc2626' : '#d97706');
    $statusIcon = $statusKey === 'success' ? 'fa-check-circle' : ($statusKey === 'failed' ? 'fa-times-circle' : 'fa-clock');
@endphp
<div class="section-card">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h3 class="section-title">
            <i class="fas fa-receipt"></i>
            Receipt #{{ $payment->transaction_id }}
        </h3>
        <a href="{{ route('citizen.transactions') }}" class="view-all">
            <i class="fas fa-arrow-left"></i> Back to History
        </a>
    </div>
    
    <div class="section-body" style="padding: 32px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 48px; color: {{ $statusColor }}; margin-bottom: 16px;">
                <i class="fas {{ $statusIcon }}"></i>
            </div>
            <h2 style="font-size: 24px; font-weight: 700; color: var(--text-primary);">Payment {{ $statusLabel }}</h2>
            <p style="color: var(--text-secondary);">
                @if($statusKey === 'success')
                    Your payment has been processed and recorded.
                @elseif($statusKey === 'failed')
                    {{ $payment->failure_reason ?? 'Payment could not be completed.' }}
                @else
                    Payment is still being processed. Please check again shortly.
                @endif
            </p>
        </div>

        <div style="background: var(--surface-secondary); border-radius: var(--radius); padding: 24px; margin-bottom: 32px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <span style="color: var(--text-secondary);">Transaction ID</span>
                <span style="font-weight: 600; font-family: monospace;">{{ $payment->transaction_id }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <span style="color: var(--text-secondary);">Date & Time</span>
                <span style="font-weight: 600;">{{ $displayDate->format('d M Y, h:i A') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <span style="color: var(--text-secondary);">Payment Method</span>
                <span style="font-weight: 600; text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <span style="color: var(--text-secondary);">Tax Type</span>
                <span style="font-weight: 600;">{{ $payment->tax_type == 'water_tax' ? 'Water Tax' : 'Property Tax' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <span style="color: var(--text-secondary);">Status</span>
                <span style="font-weight: 700; color: {{ $statusColor }};">{{ $statusLabel }}</span>
            </div>
            @if($statusKey === 'failed' && $payment->failure_reason)
                <div style="display: flex; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 16px; gap: 16px;">
                    <span style="color: var(--text-secondary);">Failure Reason</span>
                    <span style="font-weight: 600; color: #b91c1c; text-align: right;">{{ $payment->failure_reason }}</span>
                </div>
            @endif
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px;">
                <span style="color: var(--text-primary); font-weight: 700; font-size: 18px;">Amount</span>
                <span style="color: #16a34a; font-weight: 800; font-size: 24px;">₹{{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <div style="border: 1px dashed var(--border); border-radius: var(--radius); padding: 20px; color: var(--text-secondary); font-size: 14px;">
            <h4 style="color: var(--text-primary); margin-bottom: 12px; font-size: 16px;">Important Information</h4>
            <p style="margin-bottom: 8px;"><i class="fas fa-info-circle me-2"></i> This is an electronically generated receipt and does not require a physical signature.</p>
            <p style="margin-bottom: 0;"><i class="fas fa-shield-alt me-2"></i> Your payment is secure and has been updated in the Gram Panchayat records.</p>
        </div>

        <div style="margin-top: 32px; display: flex; gap: 16px;" class="d-print-none">
            <button onclick="window.print()" style="flex: 1; padding: 14px; background: var(--primary); color: white; border: none; border-radius: var(--radius); font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <a href="{{ route('citizen.dashboard') }}" style="flex: 1; padding: 14px; background: var(--surface-secondary); color: var(--text-primary); border: 1px solid var(--border); border-radius: var(--radius); font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    .citizen-sidebar, .citizen-topbar, .d-print-none { display: none !important; }
    .citizen-main { margin: 0 !important; }
    .citizen-content { padding: 0 !important; }
    .section-card { box-shadow: none !important; border: none !important; }
}
</style>
@endsection
