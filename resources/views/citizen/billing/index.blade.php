@extends('citizen.layout')

@section('title', 'Billing & Invoices')
@section('page-title', 'Billing History')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card" style="background: white; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--border);">
                <h3 style="margin: 0; font-size: 18px; color: var(--text-primary);">
                    {{ app()->getLocale() == 'hi' ? 'मेरे बिल' : (app()->getLocale() == 'mr' ? 'माझी बिले' : 'My Bills') }}
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($bills->isEmpty())
                    <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                        <i class="fas fa-file-invoice" style="font-size: 48px; margin-bottom: 16px; opacity: 0.2;"></i>
                        <p>{{ app()->getLocale() == 'hi' ? 'कोई बिल उपलब्ध नहीं है।' : (app()->getLocale() == 'mr' ? 'कोणतीही बिले उपलब्ध नाहीत.' : 'No bills available.') }}</p>
                    </div>
                @else
                    {{-- Desktop Table --}}
                    <div class="billing-table-wrap">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--surface-secondary); text-align: left;">
                                    <th style="padding: 12px 20px; font-weight: 600; font-size: 13px; color: var(--text-secondary);">{{ app()->getLocale() == 'hi' ? 'बिल क्र.' : (app()->getLocale() == 'mr' ? 'बिल क्र.' : 'Bill No') }}</th>
                                    <th style="padding: 12px 20px; font-weight: 600; font-size: 13px; color: var(--text-secondary);">{{ app()->getLocale() == 'hi' ? 'अवधि' : (app()->getLocale() == 'mr' ? 'कालावधी' : 'Period') }}</th>
                                    <th style="padding: 12px 20px; font-weight: 600; font-size: 13px; color: var(--text-secondary);">{{ app()->getLocale() == 'hi' ? 'राशि' : (app()->getLocale() == 'mr' ? 'रक्कम' : 'Amount') }}</th>
                                    <th style="padding: 12px 20px; font-weight: 600; font-size: 13px; color: var(--text-secondary);">{{ app()->getLocale() == 'hi' ? 'स्थिति' : (app()->getLocale() == 'mr' ? 'स्थिती' : 'Status') }}</th>
                                    <th style="padding: 12px 20px; font-weight: 600; font-size: 13px; color: var(--text-secondary);">{{ app()->getLocale() == 'hi' ? 'कार्रवाई' : (app()->getLocale() == 'mr' ? 'कृती' : 'Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bills as $bill)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 16px 20px;">
                                        <div style="font-weight: 500;">#{{ $bill->bill_no ?? '-' }}</div>
                                        <div style="font-size: 12px; color: var(--text-secondary);">{{ $bill->payment_date ? $bill->payment_date->format('d M Y') : '' }}</div>
                                    </td>
                                    <td style="padding: 16px 20px;">
                                        {{ $bill->period ?? '-' }}
                                    </td>
                                    <td style="padding: 16px 20px; font-weight: 600;">
                                        ₹{{ number_format($bill->monthly_bill, 2) }}
                                    </td>
                                    <td style="padding: 16px 20px;">
                                        @if($bill->hasPendingBalance())
                                            <span style="padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 500; background: #fee2e2; color: #dc2626;">{{ app()->getLocale() == 'hi' ? 'लंबित' : (app()->getLocale() == 'mr' ? 'प्रलंबित' : 'Pending') }}</span>
                                        @else
                                            <span style="padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 500; background: #dcfce7; color: #166534;">{{ app()->getLocale() == 'hi' ? 'भुगतान किया' : (app()->getLocale() == 'mr' ? 'भरले' : 'Paid') }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 16px 20px;">
                                        <a href="{{ route('citizen.billing.invoice', $bill->id) }}" class="btn-invoice" target="_blank">
                                            <i class="fas fa-print"></i> {{ app()->getLocale() == 'hi' ? 'इनवॉइस देखें' : (app()->getLocale() == 'mr' ? 'इनव्हॉइस पहा' : 'View Invoice') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="billing-mobile-cards">
                        @foreach($bills as $bill)
                        <div class="billing-mobile-card">
                            <div class="bmc-row">
                                <span class="bmc-label">{{ app()->getLocale() == 'hi' ? 'बिल क्र.' : (app()->getLocale() == 'mr' ? 'बिल क्र.' : 'Bill No') }}</span>
                                <span class="bmc-value" style="font-weight:600;">#{{ $bill->bill_no ?? '-' }}
                                    @if($bill->payment_date)
                                    <small style="font-weight:400; color:var(--text-secondary); display:block;">{{ $bill->payment_date->format('d M Y') }}</small>
                                    @endif
                                </span>
                            </div>
                            <div class="bmc-row">
                                <span class="bmc-label">{{ app()->getLocale() == 'hi' ? 'अवधि' : (app()->getLocale() == 'mr' ? 'कालावधी' : 'Period') }}</span>
                                <span class="bmc-value">{{ $bill->period ?? '-' }}</span>
                            </div>
                            <div class="bmc-row">
                                <span class="bmc-label">{{ app()->getLocale() == 'hi' ? 'राशि' : (app()->getLocale() == 'mr' ? 'रक्कम' : 'Amount') }}</span>
                                <span class="bmc-value" style="font-weight:600;">₹{{ number_format($bill->monthly_bill, 2) }}</span>
                            </div>
                            <div class="bmc-row">
                                <span class="bmc-label">{{ app()->getLocale() == 'hi' ? 'स्थिति' : (app()->getLocale() == 'mr' ? 'स्थिती' : 'Status') }}</span>
                                <span class="bmc-value">
                                    @if($bill->hasPendingBalance())
                                        <span style="padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 500; background: #fee2e2; color: #dc2626;">{{ app()->getLocale() == 'hi' ? 'लंबित' : (app()->getLocale() == 'mr' ? 'प्रलंबित' : 'Pending') }}</span>
                                    @else
                                        <span style="padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 500; background: #dcfce7; color: #166534;">{{ app()->getLocale() == 'hi' ? 'भुगतान किया' : (app()->getLocale() == 'mr' ? 'भरले' : 'Paid') }}</span>
                                    @endif
                                </span>
                            </div>
                            <div style="margin-top:12px;">
                                <a href="{{ route('citizen.billing.invoice', $bill->id) }}" class="btn-invoice" target="_blank" style="width:100%; justify-content:center;">
                                    <i class="fas fa-print"></i> {{ app()->getLocale() == 'hi' ? 'इनवॉइस देखें' : (app()->getLocale() == 'mr' ? 'इनव्हॉइस पहा' : 'View Invoice') }}
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .btn-invoice {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: var(--surface-secondary);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-invoice:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: var(--primary);
    }

    /* Desktop table visible, mobile cards hidden */
    .billing-table-wrap { display: block; }
    .billing-mobile-cards { display: none; }

    @media (max-width: 640px) {
        .billing-table-wrap { display: none; }
        .billing-mobile-cards { display: block; padding: 12px; }

        .billing-mobile-card {
            background: var(--surface-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .bmc-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 6px 0;
            border-bottom: 1px solid var(--border);
            gap: 8px;
        }

        .bmc-row:last-of-type { border-bottom: none; }

        .bmc-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .bmc-value {
            font-size: 13px;
            color: var(--text-primary);
            text-align: right;
        }
    }
</style>
@endsection
