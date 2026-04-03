@extends('admin.layouts.app')

@section('title', 'Tax Rate Adjustment')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1><i class="fas fa-percentage"></i> Tax Rate Adjustment</h1>
        <p>Increase monthly tax bills by a percentage for all or selected citizens</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <i class="fas fa-tint"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">{{ number_format($stats['total_water_records']) }}</span>
                <span class="stat-label">Water Tax Records</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-home"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">{{ number_format($stats['total_property_records']) }}</span>
                <span class="stat-label">Property Tax Records</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">{{ number_format($stats['total_citizens']) }}</span>
                <span class="stat-label">Total Citizens</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-content">
                <span class="stat-number">₹{{ number_format($stats['water_tax_total_monthly'], 2) }}</span>
                <span class="stat-label">Monthly Water Tax Total</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Form Section -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-sliders-h"></i> Apply Tax Rate Increase</h3>
            </div>
            <div class="card-body">
                <form id="taxAdjustmentForm" action="{{ route('admin.tax-rate-adjustment.apply') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Tax Type Selection -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><strong>Tax Type</strong></label>
                            <div class="tax-type-options">
                                <label class="tax-type-option">
                                    <input type="radio" name="tax_type" value="water" checked>
                                    <div class="option-content">
                                        <i class="fas fa-tint"></i>
                                        <span>Water Tax Only</span>
                                    </div>
                                </label>
                                <label class="tax-type-option">
                                    <input type="radio" name="tax_type" value="property">
                                    <div class="option-content">
                                        <i class="fas fa-home"></i>
                                        <span>Property Tax Only</span>
                                    </div>
                                </label>
                                <label class="tax-type-option">
                                    <input type="radio" name="tax_type" value="both">
                                    <div class="option-content">
                                        <i class="fas fa-layer-group"></i>
                                        <span>Both</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Percentage Input -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label" for="percentage"><strong>Increase Percentage (%)</strong></label>
                            <div class="percentage-input-wrapper">
                                <input type="number" 
                                       id="percentage" 
                                       name="percentage" 
                                       class="form-control form-control-lg" 
                                       placeholder="e.g., 10" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="100"
                                       required>
                                <span class="percentage-symbol">%</span>
                            </div>
                            <small class="text-muted">Enter percentage (0.01% to 100%)</small>
                        </div>

                        <!-- Apply To Selection -->
                        <div class="col-12 mb-4">
                            <label class="form-label"><strong>Apply To</strong></label>
                            <div class="apply-to-options">
                                <label class="apply-to-option">
                                    <input type="radio" name="apply_to" value="all" checked onchange="toggleSelectionOptions()">
                                    <div class="option-content">
                                        <i class="fas fa-globe"></i>
                                        <span>All Citizens</span>
                                    </div>
                                </label>
                                <label class="apply-to-option">
                                    <input type="radio" name="apply_to" value="selected" onchange="toggleSelectionOptions()">
                                    <div class="option-content">
                                        <i class="fas fa-filter"></i>
                                        <span>Selected Demands</span>
                                    </div>
                                </label>
                                <label class="apply-to-option">
                                    <input type="radio" name="apply_to" value="customer" onchange="toggleSelectionOptions()">
                                    <div class="option-content">
                                        <i class="fas fa-user"></i>
                                        <span>Specific Customer</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Demand Number Selection (for "Selected Only") -->
                    <div id="demandSelection" class="selection-section" style="display: none;">
                        <label class="form-label"><strong>Select Demand Numbers</strong></label>
                        <p class="text-muted mb-3">Choose which demand numbers to apply the tax increase to:</p>
                        
                        <div class="demand-numbers-grid">
                            @foreach($demandNumbers as $demandNumber)
                            <label class="demand-checkbox">
                                <input type="checkbox" name="selected_demand_numbers[]" value="{{ $demandNumber }}">
                                <span class="checkbox-content">
                                    <i class="fas fa-file-invoice"></i>
                                    Demand {{ $demandNumber }}
                                </span>
                            </label>
                            @endforeach
                        </div>

                        @if($demandNumbers->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No demand numbers found.
                        </div>
                        @endif
                    </div>
                    
                    <!-- Customer Search (for "Specific Customer") -->
                    <div id="customerSelection" class="selection-section" style="display: none;">
                        <label class="form-label"><strong>Search Customer</strong></label>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="customerNoInput" name="customer_no" class="form-control form-control-lg" placeholder="Enter Customer Number">
                            <button class="btn btn-outline-primary" type="button" onclick="checkCustomer()">Verify</button>
                        </div>
                        <div id="customerDetails" class="customer-details-card" style="display: none;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1" id="customerName">Customer Name</h5>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-tint text-primary"></i> <span id="waterCount">0</span> Water Records &bull; 
                                        <i class="fas fa-home text-success"></i> <span id="propertyCount">0</span> Property Records
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div id="customerError" class="alert alert-danger mt-2" style="display: none;">
                            <i class="fas fa-times-circle"></i> Customer not found. Please check the customer number.
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div id="previewSection" class="preview-section mt-4" style="display: none;">
                        <h4><i class="fas fa-eye"></i> Preview Changes</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">Records Affected</span>
                                <span class="preview-value" id="previewRecords">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Current Monthly Total</span>
                                <span class="preview-value" id="previewCurrent">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">New Monthly Total</span>
                                <span class="preview-value" id="previewNew">-</span>
                            </div>
                            <div class="preview-item highlight">
                                <span class="preview-label">Total Increase</span>
                                <span class="preview-value" id="previewIncrease">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions mt-4">
                        <button type="button" class="btn btn-secondary btn-lg" onclick="previewChanges()">
                            <i class="fas fa-calculator"></i> Calculate Preview
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="applyBtn" disabled>
                            <i class="fas fa-check"></i> Apply Tax Increase
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light">
                <div class="d-flex align-items-start gap-2 text-muted">
                    <i class="fas fa-info-circle mt-1 text-primary"></i>
                    <small>Calculations are rounded to 2 decimal places. A full undo is available in the History section if applied by mistake.</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- History & Undo Section -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            <div class="card-body p-0">
                @if($adjustments->isEmpty())
                <div class="text-center p-5 text-muted">
                    <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                    <p>No adjustments have been made yet.</p>
                </div>
                @else
                <div class="history-list">
                    @foreach($adjustments as $adjustment)
                    <div class="history-item {{ $adjustment->is_reverted ? 'reverted' : '' }}">
                        <div class="history-header">
                            <span class="badge bg-{{ $adjustment->tax_type == 'both' ? 'purple' : ($adjustment->tax_type == 'water' ? 'primary' : 'success') }}">
                                {{ ucfirst($adjustment->tax_type) }}
                            </span>
                            <span class="history-time">{{ $adjustment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="history-details">
                            <h4 class="increase-amount text-{{ $adjustment->is_reverted ? 'muted' : 'success' }}">
                                +{{ $adjustment->percentage }}%
                                @if($adjustment->is_reverted)
                                <span class="reverted-badge">REVERTED</span>
                                @endif
                            </h4>
                            <p class="mb-1">
                                <strong>Applied to:</strong> 
                                @if($adjustment->apply_to == 'all')
                                    All Citizens
                                @elseif($adjustment->apply_to == 'customer')
                                    Customer {{ $adjustment->filters['customer_no'] ?? 'Unknown' }}
                                @else
                                    {{ count($adjustment->filters['demand_numbers'] ?? []) }} Demand(s)
                                @endif
                            </p>
                            <p class="mb-2 text-muted small">
                                Affected {{ $adjustment->affected_records_count }} records • By {{ $adjustment->performer->name ?? 'Admin' }}
                            </p>
                            
                            @if(!$adjustment->is_reverted)
                            <form action="{{ route('admin.tax-rate-adjustment.undo', $adjustment) }}" method="POST" onsubmit="return confirm('Are you sure you want to revert this adjustment? This will decrease the current tax rates by the same percentage.')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="fas fa-undo"></i> Undo Changes
                                </button>
                            </form>
                            @else
                            <div class="revert-info">
                                <i class="fas fa-reply"></i> Reverted {{ $adjustment->reverted_at->diffForHumans() }}
                            </div>
                            @endif
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
    /* Reuse previous styles plus new ones */
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        height: 100%;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        min-width: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    .tax-type-options, .apply-to-options {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .tax-type-option .option-content, .apply-to-option .option-content {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        font-size: 14px;
        font-weight: 500;
    }
    
    .tax-type-option input:checked + .option-content,
    .apply-to-option input:checked + .option-content {
        border-color: #f97316;
        background: #fff7ed;
        color: #c2410c;
    }

    .selection-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 24px;
        margin-top: 24px;
        border: 1px solid #e2e8f0;
    }

    .demand-numbers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .demand-checkbox .checkbox-content {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        display: block;
        cursor: pointer;
        font-size: 13px;
    }
    
    .demand-checkbox input:checked + .checkbox-content {
        background: #dcfce7;
        border-color: #22c55e;
        color: #15803d;
    }
    
    .customer-details-card {
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 16px;
    }
    
    .avatar-circle {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0284c7;
        font-size: 20px;
    }

    .history-list {
        max-height: 600px;
        overflow-y: auto;
    }

    .history-item {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        transition: 0.2s;
    }

    .history-item:last-child {
        border-bottom: none;
    }
    
    .history-item.reverted {
        background: #f8fafc;
        opacity: 0.7;
    }

    .history-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .history-time {
        font-size: 12px;
        color: #94a3b8;
    }

    .increase-amount {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .reverted-badge {
        font-size: 10px;
        background: #cbd5e1;
        color: #475569;
        padding: 2px 6px;
        border-radius: 4px;
        vertical-align: middle;
    }
    
    .revert-info {
        font-size: 12px;
        color: #64748b;
        margin-top: 8px;
        padding: 4px 8px;
        background: #e2e8f0;
        border-radius: 4px;
        display: inline-block;
    }
    
    .badge.bg-purple {
        background-color: #8b5cf6;
        color: white;
    }
    
    .preview-section {
        background: linear-gradient(to right, #f8fafc, #f1f5f9);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
    }
    
    .preview-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    
    .preview-item {
        background: white;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .preview-item.highlight {
        background: #fff7ed;
        border: 1px solid #fed7aa;
    }
    
    .preview-value {
        font-weight: 700;
        font-size: 16px;
        display: block;
        margin-top: 4px;
    }
    
    .preview-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
    }

    @media (max-width: 992px) {
        .preview-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

@endsection

@push('scripts')
<script>
    function toggleSelectionOptions() {
        const applyTo = document.querySelector('input[name="apply_to"]:checked').value;
        
        document.getElementById('demandSelection').style.display = applyTo === 'selected' ? 'block' : 'none';
        document.getElementById('customerSelection').style.display = applyTo === 'customer' ? 'block' : 'none';
        
        resetPreview();
    }
    
    function resetPreview() {
        document.getElementById('previewSection').style.display = 'none';
        document.getElementById('applyBtn').disabled = true;
    }

    function checkCustomer() {
        const customerNo = document.getElementById('customerNoInput').value;
        if(!customerNo) return;
        
        fetch('{{ route("admin.tax-rate-adjustment.check-customer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ customer_no: customerNo })
        })
        .then(res => res.json())
        .then(data => {
            if(data.found) {
                document.getElementById('customerName').textContent = data.name;
                document.getElementById('waterCount').textContent = data.water_records;
                document.getElementById('propertyCount').textContent = data.property_records;
                
                document.getElementById('customerDetails').style.display = 'block';
                document.getElementById('customerError').style.display = 'none';
            } else {
                document.getElementById('customerDetails').style.display = 'none';
                document.getElementById('customerError').style.display = 'block';
            }
            resetPreview();
        });
    }

    function previewChanges() {
        const percentage = document.getElementById('percentage').value;
        const taxType = document.querySelector('input[name="tax_type"]:checked').value;
        const applyTo = document.querySelector('input[name="apply_to"]:checked').value;
        
        if (!percentage || percentage <= 0) {
            alert('Please enter a valid percentage');
            return;
        }
        
        // Prepare payload
        const payload = {
            tax_type: taxType,
            percentage: percentage,
            apply_to: applyTo
        };

        if(applyTo === 'selected') {
            const selectedDemands = [];
            document.querySelectorAll('input[name="selected_demand_numbers[]"]:checked').forEach(cb => selectedDemands.push(cb.value));
            if(selectedDemands.length === 0) {
                alert('Please select at least one demand number');
                return;
            }
            payload.selected_demand_numbers = selectedDemands;
        }
        
        if(applyTo === 'customer') {
            const customerNo = document.getElementById('customerNoInput').value;
            if(!customerNo) {
                alert('Please enter a customer number');
                return;
            }
            // Check if customer is valid (by visible details)
            if(document.getElementById('customerDetails').style.display === 'none') {
                alert('Please verify the customer first');
                return;
            }
            payload.customer_no = customerNo;
        }

        // AJAX Request
        fetch('{{ route("admin.tax-rate-adjustment.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewRecords').textContent = (data.water_records + data.property_records) + ' records';
            document.getElementById('previewCurrent').textContent = '₹' + parseFloat(data.current_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('previewNew').textContent = '₹' + parseFloat(data.new_total).toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('previewIncrease').textContent = '+₹' + parseFloat(data.increase_amount).toLocaleString('en-IN', {minimumFractionDigits: 2});

            document.getElementById('previewSection').style.display = 'block';
            if(data.water_records + data.property_records > 0) {
                document.getElementById('applyBtn').disabled = false;
            } else {
                document.getElementById('applyBtn').disabled = true;
                alert('No records found matching your criteria.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to calculate preview');
        });
    }

    // Reset preview on changes
    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('change', resetPreview);
    });
    
    // Prevent form submission via Enter key
    document.getElementById('taxAdjustmentForm').addEventListener('keypress', function(e) {
        if (e.keyCode === 13) e.preventDefault();
    });
</script>
@endpush
