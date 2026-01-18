@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <a href="{{ route('admin.tax-payments.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Tax Collection
        </a>
        <h1>Payment Details</h1>
    </div>
</div>

<div class="payment-details-grid">
    <!-- Main Payment Info -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-receipt"></i> Transaction Information</h3>
            @switch($taxPayment->payment_status)
                @case('completed')
                    <span class="badge badge-success badge-lg">Completed</span>
                    @break
                @case('pending')
                    <span class="badge badge-warning badge-lg">Pending</span>
                    @break
                @case('failed')
                    <span class="badge badge-danger badge-lg">Failed</span>
                    @break
                @default
                    <span class="badge badge-secondary badge-lg">{{ $taxPayment->payment_status }}</span>
            @endswitch
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Transaction ID</label>
                    <span class="value"><code>{{ $taxPayment->transaction_id }}</code></span>
                </div>
                
                <div class="info-item">
                    <label>Amount</label>
                    <span class="value amount-large">₹{{ number_format($taxPayment->amount, 2) }}</span>
                </div>
                
                <div class="info-item">
                    <label>Tax Type</label>
                    <span class="value">
                        <i class="fas {{ $taxPayment->taxType->icon ?? 'fa-receipt' }}"></i>
                        {{ $taxPayment->taxType->name ?? '-' }}
                    </span>
                </div>
                
                <div class="info-item">
                    <label>Period Type</label>
                    <span class="value">{{ ucfirst($taxPayment->period_type ?? '-') }}</span>
                </div>
                
                <div class="info-item">
                    <label>Period</label>
                    <span class="value">
                        @if($taxPayment->period_start && $taxPayment->period_end)
                            {{ $taxPayment->period_start->format('M d, Y') }} - {{ $taxPayment->period_end->format('M d, Y') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                
                <div class="info-item">
                    <label>Payment Method</label>
                    <span class="value">{{ $taxPayment->payment_method ?? 'PhonePe' }}</span>
                </div>
                
                @if($taxPayment->phonepe_transaction_id)
                <div class="info-item full-width">
                    <label>PhonePe Transaction ID</label>
                    <span class="value"><code>{{ $taxPayment->phonepe_transaction_id }}</code></span>
                </div>
                @endif
                
                <div class="info-item">
                    <label>Created At</label>
                    <span class="value">{{ $taxPayment->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                
                @if($taxPayment->paid_at)
                <div class="info-item">
                    <label>Paid At</label>
                    <span class="value text-success">{{ $taxPayment->paid_at->format('M d, Y H:i:s') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Citizen Details -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Citizen Details</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Name</label>
                    <span class="value">{{ $taxPayment->citizen_name }}</span>
                </div>
                
                <div class="info-item">
                    <label>Phone</label>
                    <span class="value">
                        <a href="tel:{{ $taxPayment->citizen_phone }}">{{ $taxPayment->citizen_phone }}</a>
                    </span>
                </div>
                
                @if($taxPayment->citizen_id)
                <div class="info-item">
                    <label>Citizen ID</label>
                    <span class="value">{{ $taxPayment->citizen_id }}</span>
                </div>
                @endif
                
                <div class="info-item full-width">
                    <label>Address</label>
                    <span class="value">{{ $taxPayment->citizen_address ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if($taxPayment->payment_response)
<div class="card mt-4">
    <div class="card-header">
        <h3><i class="fas fa-code"></i> Payment Response</h3>
    </div>
    <div class="card-body">
        <pre class="code-block">{{ json_encode($taxPayment->payment_response, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.page-header-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
    text-decoration: none;
    transition: color 0.15s ease;
}

.back-link:hover {
    color: #1a365d;
}

.payment-details-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 992px) {
    .payment-details-grid {
        grid-template-columns: 1fr;
    }
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.card-header h3 i {
    color: #1a365d;
}

.badge-lg {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-item .value {
    font-size: 1rem;
    color: #1e293b;
}

.info-item .value code {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    background-color: #f1f5f9;
    border-radius: 0.25rem;
}

.amount-large {
    font-size: 1.5rem !important;
    font-weight: 700;
    color: #1a365d !important;
}

.text-success {
    color: #16a34a !important;
}

.mt-4 {
    margin-top: 1.5rem;
}

.code-block {
    background-color: #1e293b;
    color: #e2e8f0;
    padding: 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    overflow-x: auto;
    margin: 0;
}
</style>
@endpush
