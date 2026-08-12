@extends('citizen.layout')

@section('title', 'Property Tax')
@section('page-title', 'Property Tax Records')

@push('styles')
<style>
    .page-header-card {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
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
        top: -50%; right: -20%;
        width: 60%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    }
    .header-content { position: relative; display: flex; align-items: center; gap: 20px; }
    .header-icon {
        width: 64px; height: 64px;
        background: rgba(255,255,255,0.2);
        border-radius: var(--radius);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }
    .header-text h2 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .header-text p { opacity: 0.9; font-size: 15px; }

    @media (max-width: 480px) {
        .page-header-card { padding: 20px 16px; margin-bottom: 16px; }
        .header-icon { width: 44px; height: 44px; font-size: 18px; }
        .header-text h2 { font-size: 17px; }
        .header-text p { font-size: 13px; }
    }

    .summary-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px; margin-bottom: 24px;
    }
    .summary-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        display: flex; align-items: center; gap: 16px;
        min-width: 0;
    }
    .summary-icon { width: 48px; height: 48px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .summary-icon.green { background: #dcfce7; color: #16a34a; }
    .summary-icon.orange { background: #ffedd5; color: #f97316; }
    .summary-icon.blue { background: #dbeafe; color: #3b82f6; }
    .summary-label { font-size: 13px; color: var(--text-secondary); margin-bottom: 4px; }
    .summary-value { font-size: 20px; font-weight: 700; color: var(--text-primary); word-break: break-word; }

    @media (max-width: 480px) {
        .summary-row { grid-template-columns: 1fr 1fr; gap: 10px; }
        .summary-card { padding: 14px; gap: 10px; }
        .summary-icon { width: 36px; height: 36px; font-size: 15px; }
        .summary-value { font-size: 16px; }
    }

    .records-card { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow); overflow: hidden; }
    .records-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .records-title { font-size: 18px; font-weight: 600; color: var(--text-primary); }

    /* Desktop table */
    .table-container { overflow-x: auto; }
    .pt-desktop-table { display: block; }
    .pt-mobile-cards { display: none; }

    .records-table { width: 100%; border-collapse: collapse; }
    .records-table th, .records-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--border); }
    .records-table th { background: var(--surface-secondary); font-size: 13px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
    .records-table tbody tr { transition: var(--transition); }
    .records-table tbody tr:hover { background: var(--surface-secondary); }
    .records-table tbody tr:last-child td { border-bottom: none; }

    /* Mobile property tax cards */
    @media (max-width: 640px) {
        .pt-desktop-table { display: none; }
        .pt-mobile-cards { display: block; padding: 12px; }

        .pt-mobile-card {
            background: var(--surface-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .pt-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .pt-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 5px 0;
            border-bottom: 1px solid var(--border);
            gap: 8px;
        }
        .pt-row:last-of-type { border-bottom: none; }

        .pt-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pt-value {
            font-size: 13px;
            color: var(--text-primary);
            text-align: right;
        }

        .pt-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .pt-actions .pay-btn,
        .pt-actions .invoice-btn {
            flex: 1;
            justify-content: center;
        }

        .records-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px;
        }
    }

    .customer-cell { display: flex; align-items: center; gap: 12px; }
    .customer-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, #10b981 0%, #34d399 100%); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
    .customer-info h4 { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 2px; }
    .customer-info p { font-size: 13px; color: var(--text-secondary); }

    .amount-cell { font-weight: 600; color: var(--text-primary); }
    .balance-cell { font-weight: 700; }
    .balance-cell.pending { color: #dc2626; }
    .balance-cell.clear { color: #16a34a; }

    .period-badge { display: inline-block; padding: 4px 10px; background: #dcfce7; color: #16a34a; font-size: 12px; font-weight: 500; border-radius: 20px; }

    .pay-btn {
        padding: 8px 16px; background: var(--secondary); color: white;
        border: none; border-radius: var(--radius-sm); font-size: 13px;
        font-weight: 600; cursor: pointer; text-decoration: none;
        transition: var(--transition); display: inline-flex; align-items: center; gap: 6px;
    }
    .pay-btn:hover { background: var(--secondary-light); transform: translateY(-1px); }
    .pay-btn:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; transform: none; }

    .invoice-btn {
        padding: 8px 16px; background: #6366f1; color: white;
        border: none; border-radius: var(--radius-sm); font-size: 13px;
        font-weight: 600; cursor: pointer; text-decoration: none;
        transition: var(--transition); display: inline-flex; align-items: center; gap: 6px;
    }
    .invoice-btn:hover { background: #4f46e5; color: white; transform: translateY(-1px); }

    .tax-breakdown { font-size: 12px; color: var(--text-secondary); }

    .empty-state { padding: 64px 24px; text-align: center; }
    .empty-state i { font-size: 64px; color: var(--text-muted); margin-bottom: 20px; }
    .empty-state h3 { font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
    .empty-state p { font-size: 15px; color: var(--text-secondary); max-width: 400px; margin: 0 auto; }
</style>
@endpush

@section('content')
{{-- Page Header --}}
<div class="page-header-card">
    <div class="header-content">
        <div class="header-icon"><i class="fas fa-home"></i></div>
        <div class="header-text">
            <h2>{{ __('messages.property_tax_records') }}</h2>
            <p>{{ __('messages.view_manage_property') }}</p>
        </div>
    </div>
</div>

{{-- Summary Row --}}
<div class="summary-row">
    <div class="summary-card">
        <div class="summary-icon green"><i class="fas fa-file-invoice"></i></div>
        <div class="summary-info">
            <div class="summary-label">{{ __('messages.total_records') }}</div>
            <div class="summary-value">{{ $propertyTaxRecords->count() }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon orange"><i class="fas fa-rupee-sign"></i></div>
        <div class="summary-info">
            <div class="summary-label">{{ __('messages.total_balance_due') }}</div>
            <div class="summary-value">₹{{ number_format($propertyTaxRecords->sum('balance'), 2) }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div class="summary-info">
            <div class="summary-label">Current Billing Period</div>
            @php
                $now = \Carbon\Carbon::now();
                $fyStart = $now->month > 3 ? $now->year : $now->year - 1;
                $periodLabel = '1 Apr ' . $fyStart . ' to 31 Mar ' . ($fyStart + 1);
                $fy = $fyStart . '-' . substr($fyStart + 1, -2);
            @endphp
            <div class="summary-value" style="font-size:14px; padding-top:4px;">{{ $periodLabel }}</div>
        </div>
    </div>
</div>

{{-- Records Table --}}
<div class="records-card">
    <div class="records-header">
        <h3 class="records-title">{{ __('messages.property_tax_records') }}</h3>
        <div style="font-size:13px; color: var(--text-secondary);">
            <i class="fas fa-info-circle me-1"></i>
            Annual billing cycle, one bill per financial year
        </div>
    </div>

    @if($propertyTaxRecords->count() > 0)
    {{-- Desktop Table --}}
    <div class="pt-desktop-table">
    <div class="table-container">
        <table class="records-table">
            <thead>
                <tr>
                    <th>{{ __('messages.a_no') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>Annual Bill Amount</th>
                    <th>Tax Breakdown</th>
                    <th>Billing Period</th>
                    <th>{{ __('messages.outstanding_amount') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($propertyTaxRecords as $record)
                @php
                    // Check if there is an annual bill for this record in the current FY
                    $annualBill = \App\Models\PropertyTaxAnnualBill::where('record_id', $record->id)
                        ->where('financial_year', $fy)
                        ->first();
                @endphp
                <tr>
                    <td>{{ $record->a_no }}</td>
                    <td>
                        <div class="customer-cell">
                            <div class="customer-avatar">
                                {{ strtoupper(substr($record->customer_name, 0, 1)) }}
                            </div>
                            <div class="customer-info">
                                <h4>{{ $record->customer_name }}</h4>
                                @if($record->property_no)
                                    <p>Prop: {{ $record->property_no }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="amount-cell">₹{{ number_format($record->current_total, 2) }}</td>
                    <td>
                        <div class="tax-breakdown">
                            <div>House: ₹{{ number_format($record->current_house_tax, 2) }}</div>
                            <div>Electricity: ₹{{ number_format($record->current_electricity_tax, 2) }}</div>
                            <div>Health: ₹{{ number_format($record->current_health_tax, 2) }}</div>
                        </div>
                    </td>
                    <td>
                        <span class="period-badge">{{ $periodLabel }}</span>
                    </td>
                    <td class="balance-cell {{ $record->balance > 0 ? 'pending' : 'clear' }}">
                        ₹{{ number_format($record->balance, 2) }}
                    </td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @if($record->balance > 0)
                            <a href="{{ route('citizen.pay-bill', ['type' => 'property', 'record_id' => $record->id]) }}"
                               class="pay-btn">
                                <i class="fas fa-credit-card"></i>
                                {{ __('messages.pay_now') }}
                            </a>
                            @else
                            <button class="pay-btn" disabled>
                                <i class="fas fa-check"></i> {{ __('messages.paid') }}
                            </button>
                            @endif

                            @if($annualBill)
                            <a href="{{ route('citizen.billing.property-invoice', $annualBill->id) }}"
                               class="invoice-btn" target="_blank">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="pt-mobile-cards">
        @foreach($propertyTaxRecords as $record)
        @php
            $annualBillMob = \App\Models\PropertyTaxAnnualBill::where('record_id', $record->id)
                ->where('financial_year', $fy)
                ->first();
        @endphp
        <div class="pt-mobile-card">
            <div class="pt-card-header">
                <div class="customer-avatar" style="width:36px;height:36px;background:linear-gradient(135deg,#10b981,#34d399);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:14px;flex-shrink:0;">
                    {{ strtoupper(substr($record->customer_name, 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div style="font-weight:600;font-size:14px;color:var(--text-primary);">{{ $record->customer_name }}</div>
                    @if($record->property_no)
                        <div style="font-size:12px;color:var(--text-secondary);">Prop: {{ $record->property_no }}</div>
                    @endif
                </div>
            </div>
            <div class="pt-row">
                <span class="pt-label">{{ __('messages.a_no') }}</span>
                <span class="pt-value">{{ $record->a_no }}</span>
            </div>
            <div class="pt-row">
                <span class="pt-label">Annual Bill</span>
                <span class="pt-value" style="font-weight:600;">₹{{ number_format($record->current_total, 2) }}</span>
            </div>
            <div class="pt-row">
                <span class="pt-label">Tax Breakdown</span>
                <span class="pt-value" style="font-size:12px;">
                    House: ₹{{ number_format($record->current_house_tax, 2) }}<br>
                    Elec: ₹{{ number_format($record->current_electricity_tax, 2) }}<br>
                    Health: ₹{{ number_format($record->current_health_tax, 2) }}
                </span>
            </div>
            <div class="pt-row">
                <span class="pt-label">Period</span>
                <span class="pt-value"><span class="period-badge">{{ $periodLabel }}</span></span>
            </div>
            <div class="pt-row">
                <span class="pt-label">{{ __('messages.outstanding_amount') }}</span>
                <span class="pt-value balance-cell {{ $record->balance > 0 ? 'pending' : 'clear' }}" style="font-weight:700;">₹{{ number_format($record->balance, 2) }}</span>
            </div>
            <div class="pt-actions">
                @if($record->balance > 0)
                    <a href="{{ route('citizen.pay-bill', ['type' => 'property', 'record_id' => $record->id]) }}" class="pay-btn">
                        <i class="fas fa-credit-card"></i> {{ __('messages.pay_now') }}
                    </a>
                @else
                    <button class="pay-btn" disabled>
                        <i class="fas fa-check"></i> {{ __('messages.paid') }}
                    </button>
                @endif
                @if($annualBillMob)
                    <a href="{{ route('citizen.billing.property-invoice', $annualBillMob->id) }}" class="invoice-btn" target="_blank">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <i class="fas fa-home"></i>
        <h3>{{ __('messages.no_property_tax_records') }}</h3>
        <p>{{ __('messages.no_records_found_contact') }}</p>
    </div>
    @endif
</div>
@endsection
