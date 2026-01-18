@extends('admin.layouts.app')

@section('title', 'Add Quick Link')

@section('content')
<div class="page-header">
    <h1>Add Quick Link</h1>
    <p>Create a new quick link for header or footer</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.quick-links.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="title">Link Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g., Privacy Policy">
            </div>
            
            <div class="form-group">
                <label for="url">URL <span class="text-danger">*</span></label>
                <input type="text" id="url" name="url" class="form-control" value="{{ old('url') }}" required placeholder="e.g., /privacy-policy or https://example.com">
            </div>
            
            <div class="form-group">
                <label for="icon">Icon (Font Awesome class)</label>
                <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon') }}" placeholder="e.g., fa-file-alt">
                <small class="text-muted">See <a href="https://fontawesome.com/icons" target="_blank">Font Awesome Icons</a></small>
            </div>
            
            <div class="form-group">
                <label for="location">Location <span class="text-danger">*</span></label>
                <select id="location" name="location" class="form-control" required>
                    <option value="footer" {{ old('location') === 'footer' ? 'selected' : '' }}>Footer</option>
                    <option value="header" {{ old('location') === 'header' ? 'selected' : '' }}>Header</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="order">Display Order</label>
                <input type="number" id="order" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
            </div>
            
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="open_new_tab" value="1" {{ old('open_new_tab') ? 'checked' : '' }}>
                    <span>Open in new tab</span>
                </label>
            </div>
            
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Link
                </button>
                <a href="{{ route('admin.quick-links.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
