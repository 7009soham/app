@extends('admin.layouts.app')

@section('title', 'Water Tax Record Details')

@section('content')
<div class="page-header">
    <a href="{{ route('admin.water-tax.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="fas fa-arrow-left me-1"></i> Back to Water Tax
    </a>
    <h1>Water Tax Record Details</h1>
    <p class="mb-0">Customer No: <strong>{{ $waterTaxRecord->customer_no }}</strong></p>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-tint me-2 text-info"></i> Record</h5>
        <a href="{{ route('admin.water-tax.edit', $waterTaxRecord) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0">
                <tbody>
                    <tr>
                        <th style="width: 220px;">A. No</th>
                        <td>{{ $waterTaxRecord->a_no }}</td>
                    </tr>
                    <tr>
                        <th>Customer Name</th>
                        <td>{{ $waterTaxRecord->customer_name }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $waterTaxRecord->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Demand</th>
                        <td>{{ $demand->name ?? ($waterTaxRecord->demand_id ? ('Demand ' . $waterTaxRecord->demand_id) : '-') }}</td>
                    </tr>
                    <tr>
                        <th>Monthly Bill</th>
                        <td>₹{{ number_format($waterTaxRecord->monthly_bill ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Period</th>
                        <td>{{ $waterTaxRecord->period ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Amount Paid</th>
                        <td class="text-success">₹{{ number_format($waterTaxRecord->amount_paid ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Balance</th>
                        <td class="{{ ($waterTaxRecord->balance ?? 0) > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">₹{{ number_format($waterTaxRecord->balance ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Oversize Charge</th>
                        <td>₹{{ number_format($waterTaxRecord->oversize_charge ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Bill No</th>
                        <td>{{ $waterTaxRecord->bill_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Receipt No</th>
                        <td>{{ $waterTaxRecord->receipt_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Date</th>
                        <td>{{ $waterTaxRecord->payment_date ? $waterTaxRecord->payment_date->format('d M Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Remarks (Shera)</th>
                        <td>{{ $waterTaxRecord->shera ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $waterTaxRecord->created_at?->format('d M Y, h:i A') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $waterTaxRecord->updated_at?->format('d M Y, h:i A') ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($waterTaxRecord->citizen)
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i> Linked Citizen</h5>
    </div>
    <div class="card-body">
        <div class="fw-semibold">{{ $waterTaxRecord->citizen->name }}</div>
        <div class="text-muted small">
            {{ $waterTaxRecord->citizen->customer_no ?? '' }}
            {{ $waterTaxRecord->citizen->phone ? ('• ' . $waterTaxRecord->citizen->phone) : '' }}
        </div>
        <div class="text-muted small">Demand: {{ $waterTaxRecord->citizen->demand->name ?? '-' }}</div>
    </div>
</div>
@endif
@endsection
