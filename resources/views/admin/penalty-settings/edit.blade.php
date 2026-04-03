@extends('admin.layouts.app')

@section('title', 'Edit Penalty Setting')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <a href="{{ route('admin.penalty-settings.index') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Penalty Settings
            </a>
            <h4 class="mt-2">Edit Penalty Setting: {{ $penaltySetting->name }}</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.penalty-settings.update', $penaltySetting) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Setting Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $penaltySetting->name) }}" placeholder="e.g., Late Payment Fee" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                            <select name="tax_type" class="form-select @error('tax_type') is-invalid @enderror" required>
                                <option value="">Select Tax Type</option>
                                <option value="water_tax" {{ old('tax_type', $penaltySetting->tax_type) == 'water_tax' ? 'selected' : '' }}>Water Tax</option>
                                <option value="property_tax" {{ old('tax_type', $penaltySetting->tax_type) == 'property_tax' ? 'selected' : '' }}>Property Tax</option>
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
                                   value="{{ old('grace_days', $penaltySetting->grace_days) }}" min="0" max="365" required>
                            <small class="text-muted">Number of days after due date before penalty applies</small>
                            @error('grace_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Penalty Percentage <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="penalty_percentage" class="form-control @error('penalty_percentage') is-invalid @enderror" 
                                       value="{{ old('penalty_percentage', $penaltySetting->penalty_percentage) }}" min="0" max="100" step="0.01" required>
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
                                  placeholder="Optional description...">{{ old('description', $penaltySetting->description) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                   id="is_active" {{ old('is_active', $penaltySetting->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Activate this setting</strong>
                                <br><small class="text-muted">Only one setting can be active per tax type. Activating this will deactivate others.</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Setting
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
                <h6><i class="fas fa-info-circle text-info me-2"></i>Current Configuration</h6>
                <ul class="small mb-0">
                    <li>Created: {{ $penaltySetting->created_at->format('d M Y') }}</li>
                    <li>Last Updated: {{ $penaltySetting->updated_at->format('d M Y, h:i A') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
