@extends('admin.layouts.app')

@section('title', 'Edit Slider')

@section('content')
<div class="page-header">
    <h1>Edit Slider</h1>
    <p>Update slider details</p>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.sliders.update', $slider) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label>Current Image</label>
                <div class="mb-3">
                    <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" style="max-width: 300px; border-radius: 8px;">
                </div>
                <label for="image">Change Image</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small class="text-muted">Leave empty to keep current image</small>
            </div>
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $slider->title) }}" placeholder="Enter slider title">
            </div>
            
            <div class="form-group">
                <label for="subtitle">Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" class="form-control" value="{{ old('subtitle', $slider->subtitle) }}" placeholder="Enter slider subtitle">
            </div>
            
            <div class="form-group">
                <label for="button_text">Button Text</label>
                <input type="text" id="button_text" name="button_text" class="form-control" value="{{ old('button_text', $slider->button_text) }}" placeholder="e.g., Learn More">
            </div>
            
            <div class="form-group">
                <label for="link">Button Link</label>
                <input type="text" id="link" name="link" class="form-control" value="{{ old('link', $slider->link) }}" placeholder="e.g., /about or https://example.com">
            </div>
            
            <div class="form-group">
                <label for="order">Display Order</label>
                <input type="number" id="order" name="order" class="form-control" value="{{ old('order', $slider->order) }}" min="0">
            </div>
            
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slider->is_active) ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>
            
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Slider
                </button>
                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
