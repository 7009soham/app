@extends('admin.layouts.app')

@section('title', 'Add Slider')

@section('content')
<div class="page-header">
    <h1>Add Slider</h1>
    <p>Create a new homepage slider</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="image">Slider Image <span class="text-danger">*</span></label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
                <small class="text-muted">Recommended size: 1920x600 pixels. Max file size: 2MB</small>
            </div>
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" placeholder="Enter slider title">
            </div>
            
            <div class="form-group">
                <label for="subtitle">Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" class="form-control" value="{{ old('subtitle') }}" placeholder="Enter slider subtitle">
            </div>
            
            <div class="form-group">
                <label for="button_text">Button Text</label>
                <input type="text" id="button_text" name="button_text" class="form-control" value="{{ old('button_text') }}" placeholder="e.g., Learn More">
            </div>
            
            <div class="form-group">
                <label for="link">Button Link</label>
                <input type="text" id="link" name="link" class="form-control" value="{{ old('link') }}" placeholder="e.g., /about or https://example.com">
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
            
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Slider
                </button>
                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
