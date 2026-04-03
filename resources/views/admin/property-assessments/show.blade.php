@extends('admin.layouts.app')

@section('title', 'Assessment Details')

@push('styles')
<style>
    .page-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px; }
    .page-title { font-size:24px;font-weight:700;color:#1e293b; }
    .btn { padding:10px 20px;border-radius:8px;font-weight:500;font-size:14px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s;text-decoration:none; }
    .btn-secondary { background:#f1f5f9;color:#64748b; }
    .btn-primary { background:linear-gradient(135deg,#7c3aed,#a855f7);color:white; }

    .detail-card { background:white;border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:24px; }
    .detail-card h3 { font-size:16px;font-weight:600;color:#1e293b;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f1f5f9; }

    .info-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px; }
    @media(max-width:1024px){.info-grid{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:576px){.info-grid{grid-template-columns:1fr;}}
    .info-item label { display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px; }
    .info-item .value { font-size:15px;font-weight:600;color:#1e293b; }
    .info-item .value.highlight { color:#7c3aed; }
    .type-badge { display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600; }
    .type-badge.regular { background:#dcfce7;color:#16a34a; }
    .type-badge.rented { background:#dbeafe;color:#2563eb; }
    .type-badge.extended { background:#ffedd5;color:#ea580c; }

    .data-table { width:100%;border-collapse:collapse; }
    .data-table th,.data-table td { padding:10px 12px;text-align:center;border:1px solid #e5e7eb;font-size:13px; }
    .data-table th { background:#f8fafc;font-weight:600;font-size:11px;color:#64748b;text-transform:uppercase; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-clipboard-list" style="color:#7c3aed;"></i> Assessment: {{ $assessment->property_number }}</h1>
    <div style="display:flex;gap:12px;">
        <a href="{{ route('admin.property-assessments.print', $assessment->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>
        <a href="{{ route('admin.property-assessments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="detail-card">
    <h3><i class="fas fa-info-circle" style="color:#7c3aed;margin-right:8px;"></i> Overview</h3>
    <div class="info-grid">
        <div class="info-item"><label>Type</label><span class="type-badge {{ $assessment->assessment_type }}">{{ $assessment->type_marathi }} ({{ ucfirst($assessment->assessment_type) }})</span></div>
        <div class="info-item"><label>Financial Year</label><div class="value">{{ $assessment->financial_year }}</div></div>
        <div class="info-item"><label>Owner</label><div class="value">{{ $assessment->owner_name }}</div></div>
        <div class="info-item"><label>Property No.</label><div class="value">{{ $assessment->property_number }}</div></div>
    </div>
</div>

<div class="detail-card">
    <h3><i class="fas fa-table" style="color:#7c3aed;margin-right:8px;"></i> Assessment Data ({{ $rows->count() }} row{{ $rows->count() > 1 ? 's' : '' }})</h3>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sr.</th><th>Prop.No</th><th>Description</th><th>Owner</th>
                    @if($assessment->assessment_type === 'rented')<th>Tenant</th>@endif
                    <th>Year</th><th>Sq.Ft</th><th>RR Land</th><th>RR Bldg</th>
                    <th>Total</th><th>Capital Value</th>
                    <th>House Tax</th><th>Light Tax</th><th>Health Tax</th><th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td>{{ $row->sr_no }}</td>
                    <td><strong>{{ $row->property_number }}</strong></td>
                    <td>{{ $row->description ?? '-' }}</td>
                    <td>{{ $row->owner_name }}</td>
                    @if($assessment->assessment_type === 'rented')<td>{{ $row->tenant_name ?? '-' }}</td>@endif
                    <td>{{ $row->year_of_construction ?? '-' }}</td>
                    <td>{{ $row->square_foot ? number_format($row->square_foot) : '-' }}</td>
                    <td>₹{{ number_format($row->rr_rate_land) }}</td>
                    <td>₹{{ number_format($row->rr_rate_building) }}</td>
                    <td>₹{{ number_format($row->total) }}</td>
                    <td>₹{{ number_format($row->capital_value) }}</td>
                    <td style="font-weight:600;">₹{{ number_format($row->house_tax) }}</td>
                    <td>₹{{ number_format($row->light_tax) }}</td>
                    <td>₹{{ number_format($row->health_tax) }}</td>
                    <td style="font-weight:700;color:#7c3aed;">₹{{ number_format($row->grand_total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
