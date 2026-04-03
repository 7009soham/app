@extends('admin.layouts.app')

@section('title', 'Property Assessments')

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
    .page-title { font-size: 24px; font-weight: 700; color: #1e293b; }
    .header-actions { display: flex; gap: 12px; }
    .btn {
        padding: 10px 20px; border-radius: 8px; text-decoration: none;
        display: inline-flex; align-items: center; gap: 8px;
        font-weight: 500; font-size: 14px; transition: all 0.3s ease;
        border: none; cursor: pointer;
    }
    .btn-primary { background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(124,58,237,0.3); }
    .btn-outline { background: white; color: #7c3aed; border: 1px solid #e5e7eb; }
    .btn-outline:hover { background: #f9fafb; border-color: #7c3aed; }

    .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 24px; }
    @media(max-width:1024px){.stats-grid{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:576px){.stats-grid{grid-template-columns:1fr;}}
    .stat-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .stat-icon { width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px; }
    .stat-icon.purple{background:#f3e8ff;color:#7c3aed;} .stat-icon.blue{background:#dbeafe;color:#2563eb;}
    .stat-icon.green{background:#dcfce7;color:#16a34a;} .stat-icon.orange{background:#ffedd5;color:#ea580c;}
    .stat-value{font-size:28px;font-weight:700;color:#1e293b;margin-bottom:4px;}
    .stat-label{font-size:14px;color:#64748b;}

    .filters-card { background:white;border-radius:12px;padding:20px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08); }
    .filters-row { display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end; }
    .filter-group { flex:1;min-width:180px; }
    .filter-group label { display:block;font-size:13px;font-weight:500;color:#64748b;margin-bottom:6px; }
    .filter-group input,.filter-group select { width:100%;padding:10px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px; }
    .filter-btn { padding:10px 20px;background:#7c3aed;color:white;border:none;border-radius:8px;font-weight:500;cursor:pointer; }
    .filter-btn.reset { background:#f1f5f9;color:#64748b; }

    .table-card { background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);overflow:hidden; }
    .data-table { width:100%;border-collapse:collapse; }
    .data-table th,.data-table td { padding:12px 14px;text-align:left;border-bottom:1px solid #f1f5f9;font-size:13px; }
    .data-table th { background:#f8fafc;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px; }
    .data-table tbody tr:hover { background:#f8fafc; }
    .customer-name { font-weight:600;color:#1e293b; }
    .amount { font-weight:600; }
    .type-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600; }
    .type-badge.regular { background:#dcfce7;color:#16a34a; }
    .type-badge.rented { background:#dbeafe;color:#2563eb; }
    .type-badge.extended { background:#ffedd5;color:#ea580c; }
    .actions-cell { display:flex;gap:5px; }
    .btn-action { padding:5px 8px;border-radius:6px;font-size:12px;text-decoration:none;border:none;cursor:pointer;transition:all 0.2s ease; }
    .btn-view { background:#eff6ff;color:#2563eb; } .btn-view:hover { background:#dbeafe; }
    .btn-edit { background:#fef3c7;color:#d97706; } .btn-edit:hover { background:#fde68a; }
    .btn-delete { background:#fee2e2;color:#dc2626; } .btn-delete:hover { background:#fecaca; }
    .btn-print { background:#f3e8ff;color:#7c3aed; } .btn-print:hover { background:#e9d5ff; }
    .btn-import { background:#ecfdf5;color:#059669;border:1px solid #a7f3d0; }
    .btn-import:hover { background:#d1fae5; }
    .pagination-wrapper { padding:20px;border-top:1px solid #f1f5f9; }

    .alert { padding:14px 20px;border-radius:10px;margin-bottom:20px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px; }
    .alert-success { background:#dcfce7;color:#166534;border:1px solid #bbf7d0; }
    .alert-error { background:#fee2e2;color:#991b1b;border:1px solid #fecaca; }

    .import-modal-overlay { display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center; }
    .import-modal-overlay.show { display:flex; }
    .import-modal { background:white;border-radius:16px;padding:32px;width:520px;max-width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);animation:slideUp 0.3s ease; }
    @keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
    .import-modal h3 { font-size:18px;font-weight:700;color:#1e293b;margin-bottom:20px; }
    .import-modal .form-group { margin-bottom:16px; }
    .import-modal .form-group label { display:block;font-size:13px;font-weight:500;color:#64748b;margin-bottom:6px; }
    .import-modal .form-group input,.import-modal .form-group select { width:100%;padding:10px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px; }
    .import-modal .form-group input:focus,.import-modal .form-group select:focus { outline:none;border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,0.1); }
    .import-modal .file-drop { border:2px dashed #d1d5db;border-radius:12px;padding:32px;text-align:center;cursor:pointer;transition:all 0.3s; }
    .import-modal .file-drop:hover,.import-modal .file-drop.dragover { border-color:#7c3aed;background:#faf5ff; }
    .import-modal .file-drop i { font-size:32px;color:#7c3aed;margin-bottom:8px;display:block; }
    .import-modal .file-drop p { font-size:14px;color:#64748b;margin:0; }
    .import-modal .file-drop .file-name { font-size:13px;color:#16a34a;font-weight:600;margin-top:8px; }
    .modal-actions { display:flex;gap:12px;justify-content:flex-end;margin-top:24px; }
    .template-link { display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#7c3aed;text-decoration:none;margin-top:8px; }
    .template-link:hover { text-decoration:underline; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-clipboard-list" style="color:#7c3aed;"></i> Property Assessment Register</h1>
    <div class="header-actions">
        <button class="btn btn-import" onclick="document.getElementById('importModal').classList.add('show')">
            <i class="fas fa-file-excel"></i> Import Excel
        </button>
        <a href="{{ route('admin.property-assessments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Assessment
        </a>
    </div>
</div>

<!-- Alerts -->
@if(session('success'))
<div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Assessments</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-home"></i></div>
        <div class="stat-value">{{ $stats['regular'] }}</div>
        <div class="stat-label">Regular</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user-friends"></i></div>
        <div class="stat-value">{{ $stats['rented'] }}</div>
        <div class="stat-label">Rented</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-expand-arrows-alt"></i></div>
        <div class="stat-value">{{ $stats['extended'] }}</div>
        <div class="stat-label">Extended</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <form method="GET" action="{{ route('admin.property-assessments.index') }}">
        <div class="filters-row">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Property No, Owner Name, Tenant" value="{{ request('search') }}">
            </div>
            <div class="filter-group" style="max-width:160px;">
                <label>Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="regular" {{ request('type')=='regular'?'selected':'' }}>Regular</option>
                    <option value="rented" {{ request('type')=='rented'?'selected':'' }}>Rented</option>
                    <option value="extended" {{ request('type')=='extended'?'selected':'' }}>Extended</option>
                </select>
            </div>
            <div class="filter-group" style="max-width:160px;">
                <label>Financial Year</label>
                <select name="fy">
                    <option value="">All Years</option>
                    @foreach($financialYears as $fy)
                        <option value="{{ $fy }}" {{ request('fy')==$fy?'selected':'' }}>{{ $fy }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.property-assessments.index') }}" class="filter-btn reset">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card">
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sr.</th>
                    <th>Property No</th>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>FY</th>
                    <th>Area (sq.ft)</th>
                    <th>House Tax</th>
                    <th>Light Tax</th>
                    <th>Health Tax</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $a)
                <tr>
                    <td>{{ $a->sr_no }}</td>
                    <td style="font-family:monospace;font-weight:600;">{{ $a->property_number }}</td>
                    <td>
                        <div class="customer-name">{{ $a->owner_name }}</div>
                        @if($a->tenant_name)
                            <div style="font-size:11px;color:#64748b;">Tenant: {{ $a->tenant_name }}</div>
                        @endif
                    </td>
                    <td><span class="type-badge {{ $a->assessment_type }}">{{ ucfirst($a->assessment_type) }}</span></td>
                    <td>{{ $a->financial_year }}</td>
                    <td>{{ $a->square_foot ? number_format($a->square_foot) : '-' }}</td>
                    <td class="amount">₹{{ number_format($a->house_tax) }}</td>
                    <td class="amount">₹{{ number_format($a->light_tax) }}</td>
                    <td class="amount">₹{{ number_format($a->health_tax) }}</td>
                    <td class="amount" style="color:#7c3aed;">₹{{ number_format($a->grand_total) }}</td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ route('admin.property-assessments.print', $a->id) }}" class="btn-action btn-print" target="_blank" title="Print">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="{{ route('admin.property-assessments.edit', $a->id) }}" class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.property-assessments.destroy', $a->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this assessment?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:40px;color:#64748b;">
                        <i class="fas fa-clipboard" style="font-size:40px;margin-bottom:16px;display:block;"></i>
                        No assessments found. Click "New Assessment" to add one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assessments->hasPages())
    <div class="pagination-wrapper">{{ $assessments->links() }}</div>
    @endif
</div>

<!-- Import Modal -->
<div class="import-modal-overlay" id="importModal" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="import-modal">
        <h3><i class="fas fa-file-excel" style="color:#059669;margin-right:8px;"></i> Import from Excel / CSV</h3>
        <form action="{{ route('admin.property-assessments.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Assessment Type *</label>
                <select name="assessment_type" required>
                    <option value="regular">Regular (नियमित)</option>
                    <option value="rented">Rented (भाडेकरू)</option>
                    <option value="extended">Extended (वाढीव)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Financial Year *</label>
                <select name="financial_year" required>
                    @php
                        $now = \Carbon\Carbon::now();
                        $startYear = $now->month > 3 ? $now->year : $now->year - 1;
                    @endphp
                    @for($y = $startYear; $y >= $startYear - 5; $y--)
                        <option value="{{ $y }}-{{ substr($y+1, -2) }}">{{ $y }}-{{ substr($y+1, -2) }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group">
                <label>CSV File *</label>
                <div class="file-drop" id="fileDrop" onclick="document.getElementById('importFile').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to select or drag & drop your CSV file</p>
                    <div class="file-name" id="fileName"></div>
                </div>
                <input type="file" name="import_file" id="importFile" accept=".csv,.txt" required style="display:none"
                       onchange="document.getElementById('fileName').textContent=this.files[0]?.name||''">
                <a href="{{ route('admin.property-assessments.download-template') }}" class="template-link">
                    <i class="fas fa-download"></i> Download sample CSV template
                </a>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('importModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Drag & drop
const drop = document.getElementById('fileDrop');
if (drop) {
    ['dragenter','dragover'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.add('dragover'); }));
    ['dragleave','drop'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.remove('dragover'); }));
    drop.addEventListener('drop', ev => {
        const file = ev.dataTransfer.files[0];
        if (file) {
            document.getElementById('importFile').files = ev.dataTransfer.files;
            document.getElementById('fileName').textContent = file.name;
        }
    });
}
</script>
@endpush
