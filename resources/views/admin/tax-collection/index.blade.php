@extends('admin.layouts.app')

@section('title', 'Tax Collection')

@section('header', 'Monthly Tax Collection')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.tax-collection.index') }}" method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select">
                            @foreach(range(date('Y')-2, date('Y')+1) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or Customer No" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Collection List ({{ $bills->total() }})</h5>
    <form action="{{ route('admin.tax-collection.store-bills') }}" method="POST" onsubmit="return confirm('Generate bills for {{ date('F Y', mktime(0,0,0, $month, 1, $year)) }}? This action cannot be undone.');">
        @csrf
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Generate Bills for Selected Month
        </button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Customer</th>
                    <th>Tax Type</th>
                    <th>Bill Period</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $bill->customer_name }}</div>
                            <div class="small text-muted">{{ $bill->customer_no }}</div>
                        </td>
                        <td>
                            @if($bill->tax_type == 'water_tax')
                                <span class="badge bg-info bg-opacity-10 text-info">Water Tax</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary">Property Tax</span>
                            @endif
                        </td>
                        <td>{{ $bill->month_name }} {{ $bill->bill_year }}</td>
                        <td>₹{{ number_format($bill->bill_amount, 2) }}</td>
                        <td class="text-success">₹{{ number_format($bill->paid_amount, 2) }}</td>
                        <td class="text-danger fw-bold">₹{{ number_format($bill->balance, 2) }}</td>
                        <td>
                            <span class="badge 
                                {{ $bill->status == 'paid' ? 'bg-success' : '' }}
                                {{ $bill->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                {{ $bill->status == 'partial' ? 'bg-info' : '' }}
                                {{ $bill->status == 'overdue' ? 'bg-danger' : '' }}
                            ">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </td>
                         <td>
                            <div class="d-flex gap-2">
                                @if($bill->balance > 0)
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#payModal{{ $bill->id }}" title="Record Payment">
                                        <i class="fas fa-money-bill-wave"></i> Pay
                                    </button>
                                @endif

                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#statusModal{{ $bill->id }}" title="Update Status">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>

                            @if($bill->balance > 0)
                                <!-- Pay Modal -->
                                <div class="modal fade" id="payModal{{ $bill->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.tax-collection.pay', $bill) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Record Payment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Details</label>
                                                        <input type="text" class="form-control" value="{{ $bill->customer_name }} - {{ $bill->tax_type }}" readonly disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Outstanding Balance</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="text" class="form-control" value="{{ $bill->balance }}" readonly disabled>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                                        <input type="number" name="amount" class="form-control" step="0.01" min="1" max="{{ $bill->balance }}" value="{{ $bill->balance }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                                        <select name="payment_method" class="form-select" required>
                                                            <option value="cash">Cash</option>
                                                            <option value="online">Online</option>
                                                            <option value="cheque">Cheque</option>
                                                            <option value="bank_transfer">Bank Transfer</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Remarks</label>
                                                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Record Payment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Status Modal -->
                            <div class="modal fade" id="statusModal{{ $bill->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tax-collection.update-status', $bill) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <small>Manually changing the status does not affect the balance or transaction history. Use "Pay" for recording payments.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Current Status</label>
                                                    <input type="text" class="form-control" value="{{ ucfirst($bill->status) }}" readonly disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">New Status <span class="text-danger">*</span></label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="pending" {{ $bill->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="partial" {{ $bill->status == 'partial' ? 'selected' : '' }}>Partial</option>
                                                        <option value="paid" {{ $bill->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                                        <option value="overdue" {{ $bill->status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No monthly bills found for this selection.</p>
                            <p>Click "Generate Bills" to create bills for this month.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())
        <div class="card-footer">
            {{ $bills->links() }}
        </div>
    @endif
</div>
@endsection
