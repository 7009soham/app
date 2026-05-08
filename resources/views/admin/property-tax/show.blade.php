@extends('admin.layouts.app')

@section('title', 'Property Tax Record Details')

@section('content')
<div class="page-header">
    <a href="{{ route('admin.property-tax.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="fas fa-arrow-left me-1"></i> Back to Property Tax
    </a>
    <h1>Property Tax Record Details</h1>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-home me-2 text-success"></i> Record</h5>
        <a href="{{ route('admin.property-tax.edit', $propertyTaxRecord) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0">
                <tbody>
                    <tr>
                        <th style="width: 220px;">A. No</th>
                        <td>{{ $propertyTaxRecord->a_no }}</td>
                    </tr>
                    <tr>
                        <th>Property No</th>
                        <td>{{ $propertyTaxRecord->property_no }}</td>
                    </tr>
                    <tr>
                        <th>Property Type</th>
                        <td>{{ $propertyTaxRecord->property_type }}</td>
                    </tr>
                    <tr>
                        <th>Customer Name</th>
                        <td>{{ $propertyTaxRecord->customer_name }}</td>
                    </tr>
                    <tr>
                        <th>Aadhaar No</th>
                        <td>{{ $propertyTaxRecord->aadhaar_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Phone No</th>
                        <td>{{ $propertyTaxRecord->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Demand</th>
                        <td>{{ $demand->name ?? ($propertyTaxRecord->demand_id ? ('Demand ' . $propertyTaxRecord->demand_id) : '-') }}</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="pt-3">Previous Taxes</th>
                    </tr>
                    <tr>
                        <th>House Tax</th>
                        <td>₹{{ number_format($propertyTaxRecord->previous_house_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Electricity Tax</th>
                        <td>₹{{ number_format($propertyTaxRecord->previous_electricity_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Health Tax</th>
                        <td>₹{{ number_format($propertyTaxRecord->previous_health_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Previous Total</th>
                        <td>₹{{ number_format($propertyTaxRecord->previous_total ?? 0, 2) }}</td>
                    </tr>

                    <tr>
                        <th colspan="2" class="pt-3">Current Taxes</th>
                    </tr>
                    <tr>
                        <th>House Tax</th>
                        <td>₹{{ number_format($propertyTaxRecord->current_house_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Electricity Tax</th>
                        <td>₹{{ number_format($propertyTaxRecord->current_electricity_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Health Tax</th>
                        <td>₹{{ number_format($propertyTaxRecord->current_health_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Current Total</th>
                        <td>₹{{ number_format($propertyTaxRecord->current_total ?? 0, 2) }}</td>
                    </tr>

                    <tr>
                        <th>Balance</th>
                        <td class="{{ ($propertyTaxRecord->balance ?? 0) > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">₹{{ number_format($propertyTaxRecord->balance ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $propertyTaxRecord->created_at?->format('d M Y, h:i A') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $propertyTaxRecord->updated_at?->format('d M Y, h:i A') ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($propertyTaxRecord->citizen)
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i> Linked Citizen</h5>
    </div>
    <div class="card-body">
        <div class="fw-semibold">{{ $propertyTaxRecord->citizen->name }}</div>
        <div class="text-muted small">Demand: {{ $propertyTaxRecord->citizen->demand->name ?? '-' }}</div>
    </div>
</div>
@endif
@endsection
