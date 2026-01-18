@extends('admin.layouts.app')

@section('title', 'Property Tax Management')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .btn-outline {
        background: white;
        color: #16a34a;
        border: 1px solid #e5e7eb;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #16a34a;
    }

    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 16px;
    }

    .stat-card .stat-icon.blue { background: #dbeafe; color: #2563eb; }
    .stat-card .stat-icon.green { background: #dcfce7; color: #16a34a; }
    .stat-card .stat-icon.yellow { background: #fef3c7; color: #d97706; }
    .stat-card .stat-icon.red { background: #fee2e2; color: #dc2626; }

    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .stat-card .stat-label {
        font-size: 14px;
        color: #64748b;
    }

    /* Filters */
    .filters-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .filters-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 6px;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .filter-btn {
        padding: 10px 20px;
        background: #16a34a;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }

    .filter-btn.reset {
        background: #f1f5f9;
        color: #64748b;
    }

    /* Table */
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }

    .data-table th {
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .customer-name {
        font-weight: 600;
        color: #1e293b;
    }

    .customer-no {
        font-size: 13px;
        color: #64748b;
        font-family: monospace;
    }

    .amount {
        font-weight: 600;
    }

    .amount.pending {
        color: #dc2626;
    }

    .amount.paid {
        color: #16a34a;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.paid {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge.pending {
        background: #fee2e2;
        color: #dc2626;
    }

    .actions-cell {
        display: flex;
        gap: 6px;
    }

    .btn-action {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-view {
        background: #eff6ff;
        color: #2563eb;
    }

    .btn-view:hover {
        background: #dbeafe;
    }

    .btn-edit {
        background: #fef3c7;
        color: #d97706;
    }

    .btn-edit:hover {
        background: #fde68a;
    }

    .btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 20px;
        border-top: 1px solid #f1f5f9;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-home" style="color: #16a34a;"></i> Property Tax Management</h1>
    <div class="header-actions">
        <a href="{{ route('admin.property-tax.export') }}" class="btn btn-outline">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="{{ route('admin.property-tax.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Record
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-building"></i></div>
        <div class="stat-value">{{ number_format($stats['total_records']) }}</div>
        <div class="stat-label">Total Properties</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-value">{{ number_format($stats['pending_count']) }}</div>
        <div class="stat-label">Pending Payments</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-rupee-sign"></i></div>
        <div class="stat-value">₹{{ number_format($stats['total_balance']) }}</div>
        <div class="stat-label">Total Outstanding</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value">₹{{ number_format($stats['total_paid']) }}</div>
        <div class="stat-label">Total Collected</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="{{ route('admin.property-tax.index') }}">
        <div class="filters-row">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Customer No, Name, Phone" value="{{ request('search') }}">
            </div>
            <div class="filter-group" style="max-width: 180px;">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.property-tax.index') }}" class="filter-btn reset">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card">
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>A.No</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Monthly Bill</th>
                    <th>Balance</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td>{{ $record->a_no }}</td>
                    <td>
                        <div class="customer-name">{{ $record->customer_name }}</div>
                        <div class="customer-no">{{ $record->customer_no }}</div>
                    </td>
                    <td>{{ $record->phone ?? 'N/A' }}</td>
                    <td>₹{{ number_format($record->monthly_bill) }}</td>
                    <td class="amount {{ $record->balance > 0 ? 'pending' : 'paid' }}">
                        ₹{{ number_format($record->balance) }}
                    </td>
                    <td class="amount paid">₹{{ number_format($record->amount_paid) }}</td>
                    <td>
                        @if($record->balance > 0)
                        <span class="status-badge pending">
                            <i class="fas fa-clock"></i> Pending
                        </span>
                        @else
                        <span class="status-badge paid">
                            <i class="fas fa-check"></i> Paid
                        </span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ route('admin.property-tax.edit', $record) }}" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.property-tax.destroy', $record) }}" method="POST" 
                                  onsubmit="return confirm('Delete this record?')" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                        <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 16px; display: block;"></i>
                        No property tax records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($records->hasPages())
    <div class="pagination-wrapper">
        {{ $records->links() }}
    </div>
    @endif
</div>
@endsection
