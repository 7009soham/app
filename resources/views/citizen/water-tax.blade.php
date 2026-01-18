@extends('citizen.layout')

@section('title', 'Water Tax')
@section('page-title', 'Water Tax Records')

@push('styles')
<style>
    /* Page Header */
    .page-header-card {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        border-radius: var(--radius-lg);
        padding: 32px;
        margin-bottom: 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .page-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    }

    .header-content {
        position: relative;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,0.2);
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .header-text h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .header-text p {
        opacity: 0.9;
        font-size: 15px;
    }

    /* Summary Cards */
    .summary-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .summary-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .summary-icon.blue { background: #dbeafe; color: #3b82f6; }
    .summary-icon.orange { background: #ffedd5; color: #f97316; }
    .summary-icon.green { background: #dcfce7; color: #16a34a; }

    .summary-info {
        flex: 1;
    }

    .summary-label {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .summary-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Records Table */
    .records-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .records-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .records-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .table-container {
        overflow-x: auto;
    }

    .records-table {
        width: 100%;
        border-collapse: collapse;
    }

    .records-table th,
    .records-table td {
        padding: 16px 20px;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }

    .records-table th {
        background: var(--surface-secondary);
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .records-table tbody tr {
        transition: var(--transition);
    }

    .records-table tbody tr:hover {
        background: var(--surface-secondary);
    }

    .records-table tbody tr:last-child td {
        border-bottom: none;
    }

    .customer-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .customer-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .customer-info h4 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .customer-info p {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .amount-cell {
        font-weight: 600;
        color: var(--text-primary);
    }

    .balance-cell {
        font-weight: 700;
    }

    .balance-cell.pending {
        color: #dc2626;
    }

    .balance-cell.clear {
        color: #16a34a;
    }

    .period-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #fef3c7;
        color: #d97706;
        font-size: 12px;
        font-weight: 500;
        border-radius: 20px;
    }

    .pay-btn {
        padding: 8px 16px;
        background: var(--secondary);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .pay-btn:hover {
        background: var(--secondary-light);
        transform: translateY(-1px);
    }

    .pay-btn:disabled {
        background: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }

    /* Empty State */
    .empty-state {
        padding: 64px 24px;
        text-align: center;
    }

    .empty-state i {
        font-size: 64px;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 15px;
        color: var(--text-secondary);
        max-width: 400px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .records-table th,
        .records-table td {
            padding: 12px 16px;
        }

        .customer-avatar {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header-card">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-tint"></i>
        </div>
        <div class="header-text">
            <h2>Water Tax Records</h2>
            <p>View and manage your water tax bills and payments</p>
        </div>
    </div>
</div>

<!-- Summary Row -->
<div class="summary-row">
    <div class="summary-card">
        <div class="summary-icon blue">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="summary-info">
            <div class="summary-label">Total Records</div>
            <div class="summary-value">{{ $waterTaxRecords->count() }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon orange">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <div class="summary-info">
            <div class="summary-label">Total Balance Due</div>
            <div class="summary-value">₹{{ number_format($waterTaxRecords->sum('balance'), 2) }}</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-icon green">
            <i class="fas fa-check-double"></i>
        </div>
        <div class="summary-info">
            <div class="summary-label">Total Paid</div>
            <div class="summary-value">₹{{ number_format($waterTaxRecords->sum('amount_paid'), 2) }}</div>
        </div>
    </div>
</div>

<!-- Records Table -->
<div class="records-card">
    <div class="records-header">
        <h3 class="records-title">All Water Tax Records</h3>
    </div>

    @if($waterTaxRecords->count() > 0)
    <div class="table-container">
        <table class="records-table">
            <thead>
                <tr>
                    <th>A.No</th>
                    <th>Customer</th>
                    <th>Monthly Bill</th>
                    <th>Period</th>
                    <th>Balance</th>
                    <th>Amount Paid</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($waterTaxRecords as $record)
                <tr>
                    <td>{{ $record->a_no }}</td>
                    <td>
                        <div class="customer-cell">
                            <div class="customer-avatar">
                                {{ strtoupper(substr($record->customer_name, 0, 1)) }}
                            </div>
                            <div class="customer-info">
                                <h4>{{ $record->customer_name }}</h4>
                                <p>ID: {{ $record->customer_no }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="amount-cell">₹{{ number_format($record->monthly_bill, 2) }}</td>
                    <td>
                        @if($record->period)
                        <span class="period-badge">{{ $record->period }}</span>
                        @else
                        <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td class="balance-cell {{ $record->balance > 0 ? 'pending' : 'clear' }}">
                        ₹{{ number_format($record->balance, 2) }}
                    </td>
                    <td class="amount-cell">₹{{ number_format($record->amount_paid, 2) }}</td>
                    <td>
                        @if($record->balance > 0)
                        <a href="{{ route('citizen.pay-bill', ['type' => 'water', 'record_id' => $record->id]) }}" class="pay-btn">
                            <i class="fas fa-credit-card"></i>
                            Pay Now
                        </a>
                        @else
                        <button class="pay-btn" disabled>
                            <i class="fas fa-check"></i>
                            Paid
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-tint-slash"></i>
        <h3>No Water Tax Records Found</h3>
        <p>There are no water tax records associated with your account. Please contact the Gram Panchayat office if you believe this is an error.</p>
    </div>
    @endif
</div>
@endsection
