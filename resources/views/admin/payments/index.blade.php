@extends('admin.layouts.app')

@section('title', 'Payments Panel')

@section('header', 'Tax Payments History')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="TXN ID or Citizen Name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="online" {{ request('method') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="cheque" {{ request('method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="bank_transfer" {{ request('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tax Type</label>
                        <select name="tax_type" class="form-select">
                            <option value="">All Taxes</option>
                            <option value="water_tax" {{ request('tax_type') == 'water_tax' ? 'selected' : '' }}>Water Tax</option>
                            <option value="property_tax" {{ request('tax_type') == 'property_tax' ? 'selected' : '' }}>Property Tax</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>TXN ID</th>
                    <th>Citizen</th>
                    <th>Tax Type</th>
                    <th>Period</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Processed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><span class="text-monospace small">{{ $payment->transaction_id }}</span></td>
                        <td>
                            <div class="fw-bold">{{ $payment->citizen->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $payment->citizen->phone ?? '' }}</div>
                        </td>
                        <td>
                            @if($payment->tax_type == 'water_tax')
                                <span class="badge bg-info bg-opacity-10 text-info">Water Tax</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary">Property Tax</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->bill)
                                {{ $payment->bill->month_name }} {{ $payment->bill->bill_year }}
                            @else
                                <span class="text-muted">Direct</span>
                            @endif
                        </td>
                        <td class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <span class="badge border text-capitalize text-dark">
                                {{ str_replace('_', ' ', $payment->payment_method) }}
                            </span>
                        </td>
                        <td>{{ $payment->paid_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($payment->processed_by)
                                <span class="small">{{ $payment->processor->name }}</span>
                            @else
                                <span class="badge bg-light text-muted">System</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
