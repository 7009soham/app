@extends('admin.layouts.app')

@section('title', 'Grievances Management')

@section('page-title', 'Grievances')

@push('styles')
<style>
    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stats-row {
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

    .stat-card .stat-icon.total { background: #dbeafe; color: #2563eb; }
    .stat-card .stat-icon.pending { background: #fef3c7; color: #d97706; }
    .stat-card .stat-icon.progress { background: #e0e7ff; color: #4f46e5; }
    .stat-card .stat-icon.resolved { background: #dcfce7; color: #16a34a; }

    .stat-card .stat-value {
        font-size: 32px;
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
        min-width: 150px;
    }

    .filter-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 6px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .filter-btn {
        padding: 10px 20px;
        background: #1e3a5f;
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

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .table-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 16px 20px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }

    .data-table th {
        background: #f8fafc;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .ticket-cell {
        font-family: monospace;
        font-weight: 600;
        color: #1e3a5f;
    }

    .category-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #f1f5f9;
        color: #475569;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.pending { background: #fef3c7; color: #d97706; }
    .status-badge.in_progress { background: #dbeafe; color: #2563eb; }
    .status-badge.resolved { background: #dcfce7; color: #16a34a; }
    .status-badge.rejected { background: #fee2e2; color: #dc2626; }

    .priority-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-badge.low { background: #f1f5f9; color: #64748b; }
    .priority-badge.medium { background: #dbeafe; color: #2563eb; }
    .priority-badge.high { background: #fef3c7; color: #d97706; }
    .priority-badge.urgent { background: #fee2e2; color: #dc2626; }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .action-btn.view {
        background: #eff6ff;
        color: #2563eb;
    }

    .action-btn.view:hover {
        background: #dbeafe;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 20px 24px;
        border-top: 1px solid #f1f5f9;
    }

    /* Empty state */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state i {
        font-size: 48px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 18px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon total"><i class="fas fa-bullhorn"></i></div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Grievances</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ $stats['pending'] }}</div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon progress"><i class="fas fa-spinner"></i></div>
        <div class="stat-value">{{ $stats['in_progress'] }}</div>
        <div class="stat-label">In Progress</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon resolved"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value">{{ $stats['resolved'] }}</div>
        <div class="stat-label">Resolved</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="{{ route('admin.grievances.index') }}">
        <div class="filters-row">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Ticket, Phone, Name" value="{{ request('search') }}">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="">All Priority</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.grievances.index') }}" class="filter-btn reset">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card">
    <div class="table-header">
        <h3 class="table-title">All Grievances</h3>
    </div>

    @if($grievances->count() > 0)
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket No</th>
                    <th>Complainant</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grievances as $grievance)
                <tr>
                    <td class="ticket-cell">{{ $grievance->ticket_no }}</td>
                    <td>
                        <div style="font-weight: 500; color: #1e293b;">{{ $grievance->name }}</div>
                        <div style="font-size: 13px; color: #64748b;">{{ $grievance->phone }}</div>
                    </td>
                    <td>
                        <span class="category-badge">
                            {{ $grievance->category_label }}
                        </span>
                    </td>
                    <td>
                        <span class="priority-badge {{ $grievance->priority }}">
                            {{ ucfirst($grievance->priority) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $grievance->status }}">
                            {{ ucfirst(str_replace('_', ' ', $grievance->status)) }}
                        </span>
                    </td>
                    <td style="font-size: 13px; color: #64748b;">
                        {{ $grievance->created_at->format('d M Y') }}<br>
                        {{ $grievance->created_at->format('h:i A') }}
                    </td>
                    <td>
                        <a href="{{ route('admin.grievances.show', $grievance) }}" class="action-btn view">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($grievances->hasPages())
    <div class="pagination-wrapper">
        {{ $grievances->links() }}
    </div>
    @endif
    @else
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No Grievances Found</h3>
        <p>No complaints matching your criteria were found.</p>
    </div>
    @endif
</div>
@endsection
