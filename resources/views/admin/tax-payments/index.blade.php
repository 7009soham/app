@extends('admin.layouts.app')

@section('title', 'Tax Collection')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1><i class="fas fa-rupee-sign"></i> Tax Collection</h1>
        <p>View and manage all tax payment records</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tax-payments.export', request()->query()) }}" class="btn btn-outline btn-sm">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">₹{{ number_format($stats['total_amount'], 2) }}</span>
            <span class="stat-label">Total Collection</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">{{ number_format($stats['total_transactions']) }}</span>
            <span class="stat-label">Completed</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">{{ number_format($stats['pending_count']) }}</span>
            <span class="stat-label">Pending</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">{{ number_format($stats['failed_count']) }}</span>
            <span class="stat-label">Failed</span>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.tax-payments.index') }}" method="GET" class="filter-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Name, Phone, Transaction ID" 
                           value="{{ request('search') }}">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tax_type">Tax Type</label>
                    <select id="tax_type" name="tax_type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($taxTypes as $type)
                            <option value="{{ $type->id }}" {{ request('tax_type') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">From Date</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                
                <div class="form-group">
                    <label for="date_to">To Date</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                
                <div class="form-group filter-actions">
                    <label>&nbsp;</label>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('admin.tax-payments.index') }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Payment Records</h3>
        <span class="badge badge-primary">{{ $payments->total() }} total records</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Citizen</th>
                        <th>Tax Type</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <code class="transaction-id">{{ $payment->transaction_id }}</code>
                            </td>
                            <td>
                                <div class="citizen-info">
                                    <span class="citizen-name">{{ $payment->citizen_name }}</span>
                                    <span class="citizen-phone">{{ $payment->citizen_phone }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="tax-type-badge">
                                    <i class="fas {{ $payment->taxType->icon ?? 'fa-receipt' }}"></i>
                                    {{ $payment->taxType->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="period-badge">{{ ucfirst($payment->period_type ?? '-') }}</span>
                            </td>
                            <td>
                                <span class="amount">₹{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>
                                @switch($payment->payment_status)
                                    @case('completed')
                                        <span class="badge badge-success">Completed</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-warning">Pending</span>
                                        @break
                                    @case('failed')
                                        <span class="badge badge-danger">Failed</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $payment->payment_status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <span class="date">{{ $payment->created_at->format('M d, Y') }}</span>
                                <span class="time">{{ $payment->created_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tax-payments.show', $payment) }}" class="btn btn-sm btn-outline" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No payment records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($payments->hasPages())
            <div class="pagination-wrapper">
                {{ $payments->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.page-header-content h1 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.page-header-content h1 i {
    color: #1a365d;
}

.page-actions {
    display: flex;
    gap: 0.5rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.filter-form {
    width: 100%;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    align-items: end;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #475569;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.375rem;
    background-color: #fff;
    transition: border-color 0.15s ease;
}

.form-control:focus {
    outline: none;
    border-color: #1a365d;
    box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
}

.btn-group {
    display: flex;
    gap: 0.5rem;
}

.filter-actions {
    display: flex;
    flex-direction: column;
}

.transaction-id {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background-color: #f1f5f9;
    border-radius: 0.25rem;
    color: #475569;
}

.citizen-info {
    display: flex;
    flex-direction: column;
}

.citizen-name {
    font-weight: 500;
    color: #1e293b;
}

.citizen-phone {
    font-size: 0.875rem;
    color: #64748b;
}

.tax-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    color: #1a365d;
}

.tax-type-badge i {
    font-size: 0.75rem;
}

.period-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    background-color: #e2e8f0;
    border-radius: 0.25rem;
    color: #475569;
}

.amount {
    font-weight: 600;
    color: #1e293b;
}

.date {
    display: block;
    font-weight: 500;
    color: #1e293b;
}

.time {
    font-size: 0.75rem;
    color: #64748b;
}

.py-4 {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.py-4 i {
    display: block;
    color: #cbd5e1;
}

.pagination-wrapper {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
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
</style>
@endpush
