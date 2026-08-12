@extends('admin.layouts.app')

@section('title', 'New Assessment')

@push('styles')
<style>
    .page-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px; }
    .page-title { font-size:24px;font-weight:700;color:#1e293b; }
    .form-card { background:white;border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:24px; }
    .form-card h3 { font-size:16px;font-weight:600;color:#1e293b;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f1f5f9; }

    .type-tabs { display:flex;gap:0;margin-bottom:24px;background:#f1f5f9;border-radius:10px;padding:4px; }
    .type-tab { flex:1;padding:12px;text-align:center;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.3s;border:none;background:transparent;color:#64748b; }
    .type-tab.active { background:white;color:#7c3aed;box-shadow:0 2px 6px rgba(0,0,0,0.08); }
    .type-tab:hover:not(.active) { color:#1e293b; }

    .form-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px; }
    @media(max-width:1024px){.form-grid{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:576px){.form-grid{grid-template-columns:1fr;}}

    .form-group { margin-bottom:0; }
    .form-group label { display:block;font-size:13px;font-weight:500;color:#64748b;margin-bottom:6px; }
    .form-group input,.form-group select { width:100%;padding:10px 14px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;transition:border-color 0.3s; }
    .form-group input:focus,.form-group select:focus { outline:none;border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,0.1); }
    .form-group.span-2 { grid-column:span 2; }

    .row-separator { grid-column:1/-1;border-top:2px dashed #e5e7eb;margin:8px 0;position:relative; }
    .row-separator span { position:absolute;top:-10px;left:20px;background:white;padding:0 10px;font-size:12px;color:#7c3aed;font-weight:600; }

    .btn { padding:10px 20px;border-radius:8px;font-weight:500;font-size:14px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s; }
    .btn-primary { background:linear-gradient(135deg,#7c3aed,#a855f7);color:white; }
    .btn-primary:hover { transform:translateY(-2px);box-shadow:0 4px 12px rgba(124,58,237,0.3); }
    .btn-secondary { background:#f1f5f9;color:#64748b; }
    .btn-secondary:hover { background:#e2e8f0; }
    .btn-success { background:#16a34a;color:white; }
    .btn-danger { background:#fee2e2;color:#dc2626; }
    .btn-danger:hover { background:#fecaca; }
    .text-decoration-none { text-decoration:none; }

    .tenant-field { display:none; }
    .tenant-field.show { display:block; }
    .add-row-section { display:none;grid-column:1/-1;text-align:center;padding:16px; }
    .add-row-section.show { display:block; }

    #rowsContainer .assessment-row { position:relative; }
    .remove-row-btn { position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;background:#fee2e2;color:#dc2626;border:none;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;z-index:2; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-plus-circle" style="color:#7c3aed;"></i> New Property Assessment</h1>
    <a href="{{ route('admin.property-assessments.index') }}" class="btn btn-secondary text-decoration-none">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<form action="{{ route('admin.property-assessments.store') }}" method="POST" id="assessmentForm">
    @csrf

    <!-- Type Selector -->
    <div class="form-card">
        <h3><i class="fas fa-tag" style="color:#7c3aed;margin-right:8px;"></i> Assessment Type</h3>
        <div class="type-tabs">
            <button type="button" class="type-tab active" data-type="regular" onclick="selectType('regular')">
                <i class="fas fa-home"></i> Regular (नियमित)
            </button>
            <button type="button" class="type-tab" data-type="rented" onclick="selectType('rented')">
                <i class="fas fa-user-friends"></i> Rented (भाडेकरू)
            </button>
            <button type="button" class="type-tab" data-type="extended" onclick="selectType('extended')">
                <i class="fas fa-expand-arrows-alt"></i> Extended (वाढीव)
            </button>
        </div>
        <input type="hidden" name="assessment_type" id="assessmentType" value="regular">

        <div class="form-grid">
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
        </div>
    </div>

    <!-- Assessment Rows -->
    <div id="rowsContainer">
        <div class="form-card assessment-row" data-row="0">
            <h3>
                <i class="fas fa-building" style="color:#7c3aed;margin-right:8px;"></i>
                <span class="row-title">Assessment Details</span>
            </h3>
            <div class="form-grid">
                <!-- Row 1: Core Info -->
                <div class="form-group">
                    <label>Sr. No. *</label>
                    <input type="number" name="sr_no[]" required min="1">
                </div>
                <div class="form-group">
                    <label>Property Number *</label>
                    <input type="text" name="property_number[]" required placeholder="e.g. 53/1">
                </div>
                <div class="form-group">
                    <label>Description of Property</label>
                    <input type="text" name="description[]" placeholder="e.g. Made with Sheets, RCC">
                </div>
                <div class="form-group">
                    <label>Owner Name *</label>
                    <input type="text" name="owner_name[]" required placeholder="Property owner name">
                </div>
                <div class="form-group tenant-field">
                    <label>Tenant Name</label>
                    <input type="text" name="tenant_name[]" placeholder="Tenant name (for rented)">
                </div>
                <div class="form-group">
                    <label>Year of Construction</label>
                    <input type="number" name="year_of_construction[]" min="1900" max="2050" placeholder="e.g. 1960">
                </div>

                <!-- Row 2: Measurements -->
                <div class="form-group">
                    <label>Length</label>
                    <input type="number" step="0.01" name="length[]" class="calc-dimension" data-row="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Width</label>
                    <input type="number" step="0.01" name="width[]" class="calc-dimension" data-row="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Square Foot</label>
                    <input type="number" step="0.01" name="square_foot[]" class="sq-foot" data-row="0" placeholder="Auto-calculated">
                </div>
                <div class="form-group">
                    <label>Square Meter</label>
                    <input type="number" step="0.01" name="square_meter[]" class="sq-meter" data-row="0" placeholder="Auto-calculated">
                </div>

                <!-- Row 3: Rates & Valuation -->
                <div class="form-group">
                    <label>RR Rate (Land)</label>
                    <input type="number" step="0.01" name="rr_rate_land[]" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>RR Rate (Building)</label>
                    <input type="number" step="0.01" name="rr_rate_building[]" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Amount with Depreciation</label>
                    <input type="number" step="0.01" name="amount_with_depreciation[]" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Total</label>
                    <input type="number" step="0.01" name="total[]" placeholder="0.00">
                </div>

                <!-- Row 4: Rates -->
                <div class="form-group">
                    <label>Rate of depriciation (घसारा दर)</label>
                    <input type="number" step="0.0001" name="rate_of_education[]" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Rate of Bearable (सहनीय दर)</label>
                    <input type="number" step="0.0001" name="rate_of_bearable[]" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Capital Value</label>
                    <input type="number" step="0.01" name="capital_value[]" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Tax Rate</label>
                    <input type="number" step="0.0001" name="tax_rate[]" placeholder="0.00">
                </div>

                <!-- Row 5: Taxes -->
                <div class="form-group">
                    <label>House Tax (घर कर)</label>
                    <input type="number" step="0.01" name="house_tax[]" class="tax-field" data-row="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Light Tax (विद्युत कर)</label>
                    <input type="number" step="0.01" name="light_tax[]" class="tax-field" data-row="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Health Tax (आरोग्य कर)</label>
                    <input type="number" step="0.01" name="health_tax[]" class="tax-field" data-row="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Grand Total</label>
                    <input type="number" step="0.01" name="grand_total[]" class="grand-total" data-row="0" placeholder="Auto-calculated" style="font-weight:700;color:#7c3aed;">
                </div>
            </div>
        </div>
    </div>

    <!-- Add Row (Extended only) -->
    <div class="add-row-section" id="addRowSection">
        <button type="button" class="btn btn-success" onclick="addExtensionRow()">
            <i class="fas fa-plus"></i> Add Extension Row
        </button>
    </div>

    <!-- Submit -->
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;">
        <a href="{{ route('admin.property-assessments.index') }}" class="btn btn-secondary text-decoration-none">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Assessment</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let rowIndex = 0;

function selectType(type) {
    document.getElementById('assessmentType').value = type;
    document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.type-tab[data-type="${type}"]`).classList.add('active');

    // Show/hide tenant fields
    document.querySelectorAll('.tenant-field').forEach(f => {
        f.classList.toggle('show', type === 'rented');
    });

    // Show/hide add row button
    document.getElementById('addRowSection').classList.toggle('show', type === 'extended');
}

function addExtensionRow() {
    rowIndex++;
    const container = document.getElementById('rowsContainer');
    const firstRow = container.querySelector('.assessment-row');
    const newRow = firstRow.cloneNode(true);

    newRow.dataset.row = rowIndex;
    newRow.querySelector('.row-title').textContent = 'Extension Row ' + rowIndex;

    // Add remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-row-btn';
    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
    removeBtn.onclick = function() { newRow.remove(); };
    newRow.appendChild(removeBtn);

    // Clear values
    newRow.querySelectorAll('input').forEach(inp => {
        inp.value = '';
        if (inp.classList.contains('calc-dimension') || inp.classList.contains('tax-field') ||
            inp.classList.contains('sq-foot') || inp.classList.contains('sq-meter') ||
            inp.classList.contains('grand-total')) {
            inp.dataset.row = rowIndex;
        }
    });

    // Show/hide tenant field based on current type
    const type = document.getElementById('assessmentType').value;
    newRow.querySelectorAll('.tenant-field').forEach(f => {
        f.classList.toggle('show', type === 'rented');
    });

    container.appendChild(newRow);
}

// Auto-calculate square footage and grand total
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('calc-dimension')) {
        const row = e.target.dataset.row;
        const rowEl = document.querySelector(`.assessment-row[data-row="${row}"]`);
        const length = parseFloat(rowEl.querySelector('[name="length[]"]').value) || 0;
        const width = parseFloat(rowEl.querySelector('[name="width[]"]').value) || 0;
        const sqFt = length * width;
        rowEl.querySelector('.sq-foot').value = sqFt ? sqFt.toFixed(2) : '';
        rowEl.querySelector('.sq-meter').value = sqFt ? (sqFt * 0.0929).toFixed(2) : '';
    }

    if (e.target.classList.contains('tax-field')) {
        const row = e.target.dataset.row;
        const rowEl = document.querySelector(`.assessment-row[data-row="${row}"]`);
        const house = parseFloat(rowEl.querySelector('[name="house_tax[]"]').value) || 0;
        const light = parseFloat(rowEl.querySelector('[name="light_tax[]"]').value) || 0;
        const health = parseFloat(rowEl.querySelector('[name="health_tax[]"]').value) || 0;
        rowEl.querySelector('.grand-total').value = (house + light + health).toFixed(2);
    }
});
</script>
@endpush
