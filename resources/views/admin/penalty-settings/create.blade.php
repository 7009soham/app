@extends('admin.layouts.app')

@section('title', 'Create Penalty Setting')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <a href="{{ route('admin.penalty-settings.index') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Penalty Settings
            </a>
            <h4 class="mt-2">Create Penalty Setting</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.penalty-settings.store') }}" method="POST">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Setting Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="e.g., Late Payment Fee" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                            <select name="tax_type" class="form-select @error('tax_type') is-invalid @enderror" required>
                                <option value="">Select Tax Type</option>
                                <option value="water_tax" {{ old('tax_type') == 'water_tax' ? 'selected' : '' }}>Water Tax</option>
                                <option value="property_tax" {{ old('tax_type') == 'property_tax' ? 'selected' : '' }}>Property Tax</option>
                            </select>
                            @error('tax_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Grace Period (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="grace_days" class="form-control @error('grace_days') is-invalid @enderror" 
                                   value="{{ old('grace_days', 10) }}" min="0" max="365" required>
                            <small class="text-muted">Number of days after due date before penalty applies</small>
                            @error('grace_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Penalty Percentage <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="penalty_percentage" class="form-control @error('penalty_percentage') is-invalid @enderror" 
                                       value="{{ old('penalty_percentage', 5) }}" min="0" max="100" step="0.01" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Percentage of outstanding balance to charge as penalty</small>
                            @error('penalty_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" 
                                  placeholder="Optional description...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                   id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Activate this setting</strong>
                                <br><small class="text-muted">Only one setting can be active per tax type. Activating this will deactivate others.</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Setting
                        </button>
                        <a href="{{ route('admin.penalty-settings.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6><i class="fas fa-lightbulb text-warning me-2"></i>Example</h6>
                <p class="small mb-2">If you set:</p>
                <ul class="small mb-0">
                    <li>Grace Period: <strong>10 days</strong></li>
                    <li>Penalty: <strong>5%</strong></li>
                </ul>
                <hr>
                <p class="small mb-0">
                    A citizen with ₹1,000 unpaid bill from January will be charged ₹50 penalty 
                    if they don't pay within 10 days after January 31st.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
