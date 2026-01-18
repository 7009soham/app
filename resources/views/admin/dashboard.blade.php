@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome back, {{ $currentAdmin->name ?? 'Admin' }}!</p>
</div>

<!-- Primary Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-success">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">₹{{ number_format($totalPayments, 2) }}</span>
            <span class="stat-label">Total Collection</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-primary">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">{{ number_format($totalTransactions) }}</span>
            <span class="stat-label">Completed Payments</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">{{ number_format($pendingPayments) }}</span>
            <span class="stat-label">Pending Payments</span>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-danger">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <span class="stat-value">{{ number_format($failedPayments) }}</span>
            <span class="stat-label">Failed Payments</span>
        </div>
    </div>
</div>

<!-- Analytics Section -->
<div class="analytics-section">
    <h3 class="section-title"><i class="fas fa-chart-line"></i> Analytics Overview</h3>
    
    <div class="analytics-grid">
        <!-- Today's Stats -->
        <div class="analytics-card today-stats">
            <div class="analytics-header">
                <i class="fas fa-calendar-day"></i>
                <span>Today's Collection</span>
            </div>
            <div class="analytics-body">
                <div class="analytics-main-value">₹{{ number_format($todayCollection, 2) }}</div>
                <div class="analytics-sub">{{ $todayTransactions }} transaction(s)</div>
            </div>
        </div>
        
        <!-- This Month's Stats -->
        <div class="analytics-card month-stats">
            <div class="analytics-header">
                <i class="fas fa-calendar-alt"></i>
                <span>This Month</span>
            </div>
            <div class="analytics-body">
                <div class="analytics-main-value">₹{{ number_format($monthCollection, 2) }}</div>
                <div class="analytics-sub">
                    {{ $monthTransactions }} transaction(s)
                    @if($monthlyGrowth != 0)
                        <span class="growth-badge {{ $monthlyGrowth >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ $monthlyGrowth >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs($monthlyGrowth) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Average Transaction -->
        <div class="analytics-card">
            <div class="analytics-header">
                <i class="fas fa-calculator"></i>
                <span>Avg. Transaction</span>
            </div>
            <div class="analytics-body">
                <div class="analytics-main-value">₹{{ number_format($averageTransaction, 2) }}</div>
                <div class="analytics-sub">Per payment</div>
            </div>
        </div>
        
        <!-- Success Rate -->
        <div class="analytics-card">
            <div class="analytics-header">
                <i class="fas fa-check-circle"></i>
                <span>Success Rate</span>
            </div>
            <div class="analytics-body">
                <div class="analytics-main-value">{{ $successRate }}%</div>
                <div class="analytics-sub">Payment completion</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $successRate }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <div class="charts-grid">
        <!-- Revenue by Tax Type Chart -->
        <div class="card chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Revenue by Tax Type</h3>
            </div>
            <div class="card-body">
                @if($revenueByTaxType->count() > 0)
                    <div class="tax-type-chart">
                        @php $totalRevenue = $revenueByTaxType->sum('total'); @endphp
                        @foreach($revenueByTaxType as $item)
                            @php 
                                $percentage = $totalRevenue > 0 ? ($item->total / $totalRevenue) * 100 : 0;
                                $colors = ['#1a365d', '#2d5a87', '#c6553b', '#d97706', '#16a34a', '#0284c7'];
                                $colorIndex = $loop->index % count($colors);
                            @endphp
                            <div class="tax-type-item">
                                <div class="tax-type-info">
                                    <span class="tax-type-color" style="background-color: {{ $colors[$colorIndex] }}"></span>
                                    <span class="tax-type-name">{{ $item->taxType->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="tax-type-stats">
                                    <span class="tax-type-amount">₹{{ number_format($item->total, 2) }}</span>
                                    <span class="tax-type-percentage">({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="tax-type-bar">
                                    <div class="tax-type-fill" style="width: {{ $percentage }}%; background-color: {{ $colors[$colorIndex] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-chart-pie"></i>
                        <p>No revenue data available</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Monthly Trend Chart -->
        <div class="card chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Monthly Revenue Trend</h3>
            </div>
            <div class="card-body">
                @if($monthlyRevenue->count() > 0)
                    <div class="monthly-chart">
                        @php 
                            $maxRevenue = $monthlyRevenue->max('total');
                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        @endphp
                        <div class="chart-bars">
                            @foreach($monthlyRevenue as $item)
                                @php $height = $maxRevenue > 0 ? ($item->total / $maxRevenue) * 100 : 0; @endphp
                                <div class="chart-bar-wrapper">
                                    <div class="chart-bar" style="height: {{ $height }}%">
                                        <div class="chart-tooltip">
                                            <strong>{{ $months[$item->month - 1] }} {{ $item->year }}</strong><br>
                                            ₹{{ number_format($item->total, 2) }}<br>
                                            {{ $item->count }} transactions
                                        </div>
                                    </div>
                                    <span class="chart-label">{{ $months[$item->month - 1] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <p>No monthly data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payment Period Distribution -->
@if($periodDistribution->count() > 0)
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clock"></i> Payment Period Distribution</h3>
    </div>
    <div class="card-body">
        <div class="period-grid">
            @foreach($periodDistribution as $period)
                <div class="period-card">
                    <div class="period-icon">
                        @switch($period->period_type)
                            @case('monthly')
                                <i class="fas fa-calendar-week"></i>
                                @break
                            @case('quarterly')
                                <i class="fas fa-calendar-alt"></i>
                                @break
                            @case('yearly')
                                <i class="fas fa-calendar"></i>
                                @break
                            @default
                                <i class="fas fa-calendar-check"></i>
                        @endswitch
                    </div>
                    <div class="period-info">
                        <span class="period-name">{{ ucfirst($period->period_type ?? 'Other') }}</span>
                        <span class="period-count">{{ $period->count }} payments</span>
                        <span class="period-amount">₹{{ number_format($period->total, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Tax Type Stats -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-file-invoice"></i> Tax Types Overview</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tax Type</th>
                        <th>Monthly Rate</th>
                        <th>Quarterly Rate</th>
                        <th>Yearly Rate</th>
                        <th>Total Payments</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxTypes as $taxType)
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <i class="fas {{ $taxType->icon ?? 'fa-receipt' }} text-primary"></i>
                                    {{ $taxType->name }}
                                </div>
                            </td>
                            <td>₹{{ number_format($taxType->monthly_rate, 2) }}</td>
                            <td>₹{{ number_format($taxType->quarterly_rate, 2) }}</td>
                            <td>₹{{ number_format($taxType->yearly_rate, 2) }}</td>
                            <td>{{ $taxType->payments_count ?? 0 }}</td>
                            <td>
                                <span class="badge {{ $taxType->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $taxType->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No tax types found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Transactions</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Citizen Name</th>
                        <th>Phone</th>
                        <th>Tax Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td><code>{{ $transaction->transaction_id }}</code></td>
                            <td>{{ $transaction->citizen_name }}</td>
                            <td>{{ $transaction->citizen_phone }}</td>
                            <td>{{ $transaction->taxType->name ?? '-' }}</td>
                            <td>₹{{ number_format($transaction->amount, 2) }}</td>
                            <td>
                                @switch($transaction->payment_status)
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
                                        <span class="badge badge-secondary">{{ $transaction->payment_status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Analytics Section */
.analytics-section {
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #1a365d;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.analytics-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.analytics-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.analytics-card.today-stats {
    border-left: 4px solid #16a34a;
}

.analytics-card.month-stats {
    border-left: 4px solid #1a365d;
}

.analytics-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 1rem;
}

.analytics-header i {
    font-size: 1rem;
}

.analytics-body .analytics-main-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.analytics-sub {
    font-size: 0.875rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.growth-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.growth-badge.positive {
    background-color: #dcfce7;
    color: #16a34a;
}

.growth-badge.negative {
    background-color: #fee2e2;
    color: #dc2626;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background-color: #e2e8f0;
    border-radius: 3px;
    margin-top: 0.75rem;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #1a365d, #2d5a87);
    border-radius: 3px;
    transition: width 0.5s ease;
}

/* Charts Section */
.charts-section {
    margin-bottom: 2rem;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.chart-card {
    min-height: 350px;
}

.chart-card .card-header h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-card .card-header h3 i {
    color: #1a365d;
}

/* Tax Type Chart */
.tax-type-chart {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tax-type-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.tax-type-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tax-type-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

.tax-type-name {
    font-weight: 500;
    color: #334155;
}

.tax-type-stats {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tax-type-amount {
    font-weight: 600;
    color: #1e293b;
}

.tax-type-percentage {
    font-size: 0.875rem;
    color: #64748b;
}

.tax-type-bar {
    width: 100%;
    height: 8px;
    background-color: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.tax-type-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* Monthly Chart */
.monthly-chart {
    height: 250px;
    display: flex;
    align-items: flex-end;
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    width: 100%;
    height: 100%;
    gap: 0.5rem;
}

.chart-bar-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    flex: 1;
}

.chart-bar {
    width: 100%;
    max-width: 40px;
    background: linear-gradient(180deg, #1a365d, #2d5a87);
    border-radius: 4px 4px 0 0;
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: auto;
}

.chart-bar:hover {
    background: linear-gradient(180deg, #0f2440, #1a365d);
}

.chart-bar:hover .chart-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-8px);
}

.chart-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-4px);
    background-color: #1e293b;
    color: #ffffff;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.75rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.chart-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #1e293b;
}

.chart-label {
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: #64748b;
}

/* Period Distribution */
.period-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.period-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background-color: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.period-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #1a365d;
    border-radius: 10px;
    color: #ffffff;
    font-size: 1.25rem;
}

.period-info {
    display: flex;
    flex-direction: column;
}

.period-name {
    font-weight: 600;
    color: #1e293b;
}

.period-count {
    font-size: 0.875rem;
    color: #64748b;
}

.period-amount {
    font-weight: 600;
    color: #1a365d;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: #94a3b8;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state p {
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
