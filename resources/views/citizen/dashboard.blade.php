@extends('citizen.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        margin-bottom: 24px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 80%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .welcome-content {
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .welcome-text h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .welcome-text p {
        font-size: 16px;
        opacity: 0.9;
    }

    .welcome-text .customer-no {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.2);
        padding: 8px 16px;
        border-radius: 20px;
        margin-top: 12px;
        font-size: 14px;
        font-weight: 500;
    }

    .pay-now-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        background: var(--secondary);
        color: white;
        text-decoration: none;
        border-radius: var(--radius);
        font-size: 16px;
        font-weight: 600;
        transition: var(--transition);
        box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
    }

    .pay-now-btn:hover {
        background: var(--secondary-light);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(249, 115, 22, 0.5);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: var(--transition);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.water {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        color: white;
    }

    .stat-icon.property {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        color: white;
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
        color: white;
    }

    .stat-icon.paid {
        background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
        color: white;
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .stat-change {
        font-size: 13px;
        margin-top: 8px;
    }

    .stat-change.pending {
        color: #dc2626;
    }

    .stat-change.success {
        color: #16a34a;
    }

    /* Mobile Improvements for Stats */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            font-size: 18px;
            border-radius: 10px;
        }

        .stat-value {
            font-size: 20px;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            margin-bottom: 2px;
        }
        
        .stat-change {
            font-size: 11px;
            margin-top: 4px;
        }
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Section Card */
    .section-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .section-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--primary);
    }

    .view-all {
        font-size: 14px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: var(--transition);
    }

    .view-all:hover {
        color: var(--secondary);
    }

    .section-body {
        padding: 0;
    }

    /* Tax Record Item */
    .tax-record {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        transition: var(--transition);
    }

    .tax-record:last-child {
        border-bottom: none;
    }

    .tax-record:hover {
        background: var(--surface-secondary);
    }

    .tax-info {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .tax-type-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .tax-type-icon.water {
        background: #dbeafe;
        color: #3b82f6;
    }

    .tax-type-icon.property {
        background: #dcfce7;
        color: #16a34a;
    }

    .tax-details h4 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .tax-details p {
        font-size: 13px;
        color: var(--text-secondary);
    }

    .tax-amount {
        text-align: right;
    }

    .tax-amount .amount {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .tax-amount .status {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        margin-top: 4px;
        display: inline-block;
    }

    .tax-amount .status.pending {
        background: #fef3c7;
        color: #d97706;
    }

    .tax-amount .status.paid {
        background: #dcfce7;
        color: #16a34a;
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
    }

    .pay-btn:hover {
        background: var(--secondary-light);
    }

    /* Empty State */
    .empty-state {
        padding: 48px 24px;
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 48px;
        color: var(--text-muted);
        margin-bottom: 16px;
    }

    .empty-state h4 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
        padding: 24px;
    }

    .action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 16px;
        background: var(--surface-secondary);
        border-radius: var(--radius);
        text-decoration: none;
        color: var(--text-primary);
        transition: var(--transition);
    }

    .action-card:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }

    .action-card i {
        font-size: 28px;
        margin-bottom: 12px;
        color: var(--primary);
        transition: var(--transition);
    }

    .action-card:hover i {
        color: white;
    }

    .action-card span {
        font-size: 14px;
        font-weight: 500;
        text-align: center;
    }
</style>
@endpush

@section('content')
<!-- Welcome Section -->
<div class="welcome-section">
    <div class="welcome-content" style="align-items: center;">
        <div class="welcome-text">
            <h2 style="font-size: 22px; margin: 0;">{{ __('messages.welcome_user', ['name' => $citizen->name]) }}</h2>
        </div>
        @if($totalBalance > 0)
        <a href="{{ route('citizen.water-tax') }}" class="pay-now-btn" style="padding: 10px 24px; margin-top: 0;">
            <i class="fas fa-credit-card"></i>
            {{ __('messages.pay_amount', ['amount' => number_format($totalBalance, 2)]) }}
        </a>
        @endif
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon water">
            <i class="fas fa-tint"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.water_tax_balance') }}</div>
            <div class="stat-value">₹{{ number_format($totalWaterTaxBalance, 2) }}</div>
            @if($totalWaterTaxBalance > 0)
            <div class="stat-change pending">
                <i class="fas fa-exclamation-circle"></i> {{ __('messages.pending_payment') }}
            </div>
            @else
            <div class="stat-change success">
                <i class="fas fa-check-circle"></i> {{ __('messages.all_paid') }}
            </div>
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon property">
            <i class="fas fa-home"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.property_tax_balance') }}</div>
            <div class="stat-value">₹{{ number_format($totalPropertyTaxBalance, 2) }}</div>
            @if($totalPropertyTaxBalance > 0)
            <div class="stat-change pending">
                <i class="fas fa-exclamation-circle"></i> {{ __('messages.pending_payment') }}
            </div>
            @else
            <div class="stat-change success">
                <i class="fas fa-check-circle"></i> {{ __('messages.all_paid') }}
            </div>
            @endif
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon total">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.total_outstanding') }}</div>
            <div class="stat-value">₹{{ number_format($totalBalance, 2) }}</div>
            <div class="stat-change pending">
                @if($totalBalance > 0)
                <i class="fas fa-clock"></i> {{ __('messages.payment_due') }}
                @else
                <i class="fas fa-check-circle"></i> {{ __('messages.clear') }}
                @endif
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon paid">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ __('messages.total_payments_made') }}</div>
            <div class="stat-value">{{ $paymentHistory->count() }}</div>
            <div class="stat-change success">
                <i class="fas fa-history"></i> {{ __('messages.recent_transactions') }}
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section-card" style="margin-bottom: 24px;">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-bolt"></i>
            {{ __('messages.quick_actions') }}
        </h3>
    </div>
    <div class="quick-actions">
        <a href="{{ route('citizen.water-tax') }}" class="action-card">
            <i class="fas fa-tint"></i>
            <span>{{ __('messages.water_tax') }}</span>
        </a>
        <a href="{{ route('citizen.property-tax') }}" class="action-card">
            <i class="fas fa-home"></i>
            <span>{{ __('messages.property_tax') }}</span>
        </a>
        <a href="{{ route('citizen.transactions') }}" class="action-card">
            <i class="fas fa-exchange-alt"></i>
            <span>{{ __('messages.transactions') }}</span>
        </a>
        <a href="{{ route('citizen.profile') }}" class="action-card">
            <i class="fas fa-user-cog"></i>
            <span>{{ __('messages.my_profile') }}</span>
        </a>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- Water Tax Records -->
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-tint"></i>
                {{ __('messages.water_tax_records') }}
            </h3>
            <a href="{{ route('citizen.water-tax') }}" class="view-all">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="section-body">
            @forelse($waterTaxRecords->take(3) as $record)
            <div class="tax-record">
                <div class="tax-info">
                    <div class="tax-type-icon water">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="tax-details">
                        <h4>{{ $record->customer_name }}</h4>
                        <p>{{ __('messages.bill_per_month', ['amount' => number_format($record->monthly_bill, 2)]) }} 
                           @if($record->period) | {{ $record->period }} @endif</p>
                    </div>
                </div>
                <div class="tax-amount">
                    @if($record->balance > 0)
                    <div class="amount">₹{{ number_format($record->balance, 2) }}</div>
                    <span class="status pending">{{ __('messages.pending') }}</span>
                    @else
                    <div class="amount">₹0.00</div>
                    <span class="status paid">{{ __('messages.paid') }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-tint-slash"></i>
                <h4>{{ __('messages.no_water_tax_records') }}</h4>
                <p>{{ __('messages.no_records_desc') }}</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Property Tax Records -->
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-home"></i>
                {{ __('messages.property_tax_records') }}
            </h3>
            <a href="{{ route('citizen.property-tax') }}" class="view-all">
                {{ __('messages.view_all') }} <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="section-body">
            @forelse($propertyTaxRecords->take(3) as $record)
            <div class="tax-record">
                <div class="tax-info">
                    <div class="tax-type-icon property">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="tax-details">
                        <h4>{{ $record->customer_name }}</h4>
                        <p>{{ __('messages.bill_per_month', ['amount' => number_format($record->monthly_bill, 2)]) }} 
                           @if($record->period) | {{ $record->period }} @endif</p>
                    </div>
                </div>
                <div class="tax-amount">
                    @if($record->balance > 0)
                    <div class="amount">₹{{ number_format($record->balance, 2) }}</div>
                    <span class="status pending">{{ __('messages.pending') }}</span>
                    @else
                    <div class="amount">₹0.00</div>
                    <span class="status paid">{{ __('messages.paid') }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-home"></i>
                <h4>{{ __('messages.no_property_tax_records') }}</h4>
                <p>{{ __('messages.no_records_desc') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</div>


@endsection
