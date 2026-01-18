@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <span>Payment Details</span>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Transaction Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Transaction ID</div>
                    <div class="col-sm-8 fw-bold">{{ $payment->transaction_id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Payment Method</div>
                    <div class="col-sm-8 text-capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Amount Paid</div>
                    <div class="col-sm-8 text-success fw-bold">₹{{ number_format($payment->amount, 2) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Status</div>
                    <div class="col-sm-8">
                        <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Payment Date</div>
                    <div class="col-sm-8">{{ $payment->paid_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Processed By</div>
                    <div class="col-sm-8">{{ $payment->processor->name ?? 'System' }}</div>
                </div>
                @if($payment->remarks)
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Remarks</div>
                    <div class="col-sm-8">{{ $payment->remarks }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($payment->bill)
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Linked Monthly Bill</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Bill Month/Year</div>
                    <div class="col-sm-8">{{ $payment->bill->month_name }} {{ $payment->bill->bill_year }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Tax Type</div>
                    <div class="col-sm-8 text-capitalize">{{ str_replace('_', ' ', $payment->tax_type) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Bill Amount</div>
                    <div class="col-sm-8">₹{{ number_format($payment->bill->bill_amount, 2) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Remaining Balance</div>
                    <div class="col-sm-8 text-danger">₹{{ number_format($payment->bill->balance, 2) }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Citizen Details</h5>
            </div>
            <div class="card-body">
                @if($payment->citizen)
                    <div class="text-center mb-3">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px; font-size: 24px;">
                            {{ strtoupper(substr($payment->citizen->name, 0, 1)) }}
                        </div>
                        <h6 class="mb-0">{{ $payment->citizen->name }}</h6>
                        <small class="text-muted">{{ $payment->citizen->customer_no }}</small>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <i class="fas fa-phone me-2 text-muted"></i> {{ $payment->citizen->phone }}
                    </div>
                    @if($payment->citizen->email)
                    <div class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i> {{ $payment->citizen->email }}
                    </div>
                    @endif
                    <div class="mb-0">
                        <i class="fas fa-map-marker-alt me-2 text-muted"></i> {{ $payment->citizen->address ?? 'N/A' }}
                    </div>
                @else
                    <p class="text-muted">Citizen information not available.</p>
                @endif
            </div>
        </div>
        
        <div class="d-grid">
            <button onclick="window.print()" class="btn btn-primary d-print-none">
                <i class="fas fa-print me-2"></i> Print Receipt
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .btn { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background: none !important; border-bottom: 2px solid #eee !important; }
}
</style>
@endsection
