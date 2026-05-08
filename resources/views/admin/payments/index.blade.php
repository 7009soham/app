@extends('admin.layouts.app')

@section('title', 'Payments Panel')

@section('header', 'Tax Payments History')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="TXN ID or Citizen Name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Demand</label>
                        <select name="demand_id" class="form-select">
                            <option value="">All</option>
                            @foreach($demands as $demand)
                                <option value="{{ $demand->id }}" {{ (string) request('demand_id') === (string) $demand->id ? 'selected' : '' }}>
                                    {{ $demand->name ?? ('Demand ' . $demand->id) }}
                                </option>
                            @endforeach
                        </select>
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="fas fa-list"></i> Payments List</h5>
        <div class="d-flex gap-2">
            <select id="bulkActionSelect" class="form-select form-select-sm" style="width: auto;">
                <option value="">Bulk Actions</option>
                <option value="export">Export Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button type="button" id="applyBulkActionBtn" class="btn btn-sm btn-outline-primary">Apply</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
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
                        <td><input type="checkbox" class="row-checkbox" value="{{ $payment->id }}"></td>
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
                        <td colspan="9" class="text-center py-5 text-muted">No payments found.</td>
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

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
    
    document.getElementById('applyBulkActionBtn')?.addEventListener('click', function() {
        let action = document.getElementById('bulkActionSelect').value;
        if (!action) {
            alert('Please select a bulk action');
            return;
        }
        
        let selected = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => selected.push(cb.value));
        
        if (selected.length === 0) {
            alert('Please select at least one record');
            return;
        }
        
        if (confirm('Are you sure you want to ' + action + ' ' + selected.length + ' records?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.payments.bulk") }}';
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            let actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            selected.forEach(id => {
                let idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'ids[]';
                idInput.value = id;
                form.appendChild(idInput);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endpush
