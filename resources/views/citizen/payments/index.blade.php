@extends('citizen.layout')

@section('title', 'Transaction History')
@section('page-title', 'My Transactions')

@section('content')
<div class="section-card">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-history"></i>
            Recent Transactions
        </h3>
    </div>
    <div class="section-body">
        <div class="table-responsive" style="padding: 0;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--surface-secondary);">
                    <tr>
                        <th style="padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-secondary);">TXN ID</th>
                        <th style="padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-secondary);">Tax Type</th>
                        <th style="padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-secondary);">Period</th>
                        <th style="padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-secondary);">Amount</th>
                        <th style="padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-secondary);">Method</th>
                        <th style="padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 600; color: var(--text-secondary);">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 16px 24px;">
                                <span style="font-family: monospace; font-size: 13px;">{{ $payment->transaction_id }}</span>
                            </td>
                            <td style="padding: 16px 24px;">
                                <span class="badge {{ $payment->tax_type == 'water_tax' ? 'bg-info' : 'bg-primary' }} bg-opacity-10 {{ $payment->tax_type == 'water_tax' ? 'text-info' : 'text-primary' }}" style="padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;">
                                    {{ $payment->tax_type == 'water_tax' ? 'Water Tax' : 'Property Tax' }}
                                </span>
                            </td>
                            <td style="padding: 16px 24px;">
                                @if($payment->bill)
                                    {{ $payment->bill->month_name }} {{ $payment->bill->bill_year }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td style="padding: 16px 24px;">
                                <strong style="color: #16a34a;">₹{{ number_format($payment->amount, 2) }}</strong>
                            </td>
                            <td style="padding: 16px 24px;">
                                <span style="text-transform: capitalize; font-size: 13px;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                            </td>
                            <td style="padding: 16px 24px; font-size: 13px; color: var(--text-secondary);">
                                {{ $payment->paid_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 48px 24px; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-receipt" style="font-size: 40px; margin-bottom: 16px; display: block; opacity: 0.3;"></i>
                                <p>You haven't made any payments yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--border);">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
