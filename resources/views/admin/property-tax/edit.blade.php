@extends('admin.layouts.app')

@section('title', 'Edit Property Tax Record')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .page-header {
        margin-bottom: 24px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .back-link:hover {
        color: #16a34a;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        max-width: 900px;
    }

    .form-body {
        padding: 32px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .required::after {
        content: ' *';
        color: #dc2626;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s ease;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }

    /* Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 16px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px;
        font-size: 15px;
        color: #374151;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }

    .select2-dropdown {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: #16a34a;
        outline: none;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 24px 32px;
        background: #f9fafb;
        border-top: 1px solid #f1f5f9;
    }

    .btn-cancel {
        padding: 12px 24px;
        background: white;
        color: #64748b;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
    }

    .btn-submit {
        padding: 12px 24px;
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin: 24px 0 16px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }

    .section-title:first-child {
        margin-top: 0;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <a href="{{ route('admin.property-tax.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Property Tax
    </a>
    <h1 class="page-title">Edit Property Tax Record</h1>
</div>

<form action="{{ route('admin.property-tax.update', $propertyTaxRecord) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="form-card">
        <div class="form-body">
            <h3 class="section-title">Basic Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">A.No</label>
                    <input type="number" name="a_no" class="form-input" value="{{ old('a_no', $propertyTaxRecord->a_no) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Property No</label>
                    <input type="text" name="property_no" class="form-input" value="{{ old('property_no', $propertyTaxRecord->property_no) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Property Type</label>
                    <input type="text" name="property_type" class="form-input" value="{{ old('property_type', $propertyTaxRecord->property_type) }}">
                </div>
                <div class="form-group">
                    <label class="form-label required">Customer Name</label>
                    <input type="text" name="customer_name" class="form-input" value="{{ old('customer_name', $propertyTaxRecord->customer_name) }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Aadhaar Number</label>
                <input type="text" name="aadhaar_no" class="form-input" value="{{ old('aadhaar_no', $propertyTaxRecord->aadhaar_no) }}" maxlength="12">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Phone Number (Optional)</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone', $propertyTaxRecord->phone) }}" maxlength="20" placeholder="Enter phone number">
            </div>

            <h3 class="section-title">Previous Year Tax</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">House Tax</label>
                    <input type="number" name="previous_house_tax" class="form-input" value="{{ old('previous_house_tax', $propertyTaxRecord->previous_house_tax) }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Electricity Tax</label>
                    <input type="number" name="previous_electricity_tax" class="form-input" value="{{ old('previous_electricity_tax', $propertyTaxRecord->previous_electricity_tax) }}" step="0.01">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Health Tax</label>
                    <input type="number" name="previous_health_tax" class="form-input" value="{{ old('previous_health_tax', $propertyTaxRecord->previous_health_tax) }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Total</label>
                    <input type="number" name="previous_total" class="form-input" value="{{ old('previous_total', $propertyTaxRecord->previous_total) }}" step="0.01">
                </div>
            </div>

            <h3 class="section-title">Current Year Tax</h3>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">House Tax</label>
                    <input type="number" name="current_house_tax" class="form-input" value="{{ old('current_house_tax', $propertyTaxRecord->current_house_tax) }}" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Electricity Tax</label>
                    <input type="number" name="current_electricity_tax" class="form-input" value="{{ old('current_electricity_tax', $propertyTaxRecord->current_electricity_tax) }}" step="0.01" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Health Tax</label>
                    <input type="number" name="current_health_tax" class="form-input" value="{{ old('current_health_tax', $propertyTaxRecord->current_health_tax) }}" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Total</label>
                    <input type="number" name="current_total" class="form-input" value="{{ old('current_total', $propertyTaxRecord->current_total) }}" step="0.01" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label required">Balance Due</label>
                <input type="number" name="balance" class="form-input" value="{{ old('balance', $propertyTaxRecord->balance) }}" step="0.01" required>
            </div>

            <h3 class="section-title">Citizen & Demand Linking</h3>

            <div class="form-row">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Link to Citizen (Optional)</label>
                    <select name="citizen_id" id="citizen_select" class="form-select" style="width: 100%;">
                        <option value="">-- Not Linked --</option>
                        @foreach($citizens as $citizen)
                        <option value="{{ $citizen->id }}" {{ old('citizen_id', $propertyTaxRecord->citizen_id) == $citizen->id ? 'selected' : '' }}>
                            {{ $citizen->name }}
                        </option>
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 13px; margin-top: 6px; display: block;">
                        <i class="fas fa-info-circle"></i> Search by name
                    </small>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Demand (Optional)</label>
                    <select name="demand_id" class="form-select">
                        <option value="">-- No Demand --</option>
                        @foreach($demands as $demand)
                        <option value="{{ $demand->id }}" {{ old('demand_id', $propertyTaxRecord->demand_id) == $demand->id ? 'selected' : '' }}>
                            {{ $demand->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.property-tax.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Update Record
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<!-- jQuery is required for Select2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 with search
        $('#citizen_select').select2({
            placeholder: '-- Search citizens by name --',
            allowClear: true,
            width: '100%',
            theme: 'default',
            minimumInputLength: 0,
            language: {
                noResults: function() {
                    return "No citizens found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });

        const toNumber = (value) => {
            const parsed = parseFloat(value);
            return Number.isNaN(parsed) ? 0 : parsed;
        };

        const updatePreviousTotal = () => {
            const total =
                toNumber($('input[name="previous_house_tax"]').val()) +
                toNumber($('input[name="previous_electricity_tax"]').val()) +
                toNumber($('input[name="previous_health_tax"]').val());

            $('input[name="previous_total"]').val(total.toFixed(2));
            updateBalanceDue();
        };

        const updateCurrentTotal = () => {
            const total =
                toNumber($('input[name="current_house_tax"]').val()) +
                toNumber($('input[name="current_electricity_tax"]').val()) +
                toNumber($('input[name="current_health_tax"]').val());

            $('input[name="current_total"]').val(total.toFixed(2));
            updateBalanceDue();
        };

        const updateBalanceDue = () => {
            const balanceDue =
                toNumber($('input[name="previous_total"]').val()) +
                toNumber($('input[name="current_total"]').val());

            $('input[name="balance"]').val(balanceDue.toFixed(2));
        };

        $('input[name="previous_house_tax"], input[name="previous_electricity_tax"], input[name="previous_health_tax"]')
            .on('input', updatePreviousTotal);

        $('input[name="current_house_tax"], input[name="current_electricity_tax"], input[name="current_health_tax"]')
            .on('input', updateCurrentTotal);

        $('input[name="previous_total"], input[name="current_total"]')
            .on('input', updateBalanceDue);

        updatePreviousTotal();
        updateCurrentTotal();
        updateBalanceDue();
    });
</script>
@endpush
