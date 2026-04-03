@extends('admin.layouts.app')

@section('title', 'Add Demand')

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <h1 class="page-title" style="font-size: 24px; font-weight: 700; color: #1e293b;">
        <a href="{{ route('admin.demands.index') }}" style="color: #64748b; margin-right: 10px; font-size: 20px;"><i class="fas fa-arrow-left"></i></a>
        Add New Demand
    </h1>
</div>

<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 32px; max-width: 600px;">
    <form action="{{ route('admin.demands.store') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Demand Name (e.g., Demand 1)</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
            @error('name') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Description</label>
            <textarea name="description" rows="3"
                      style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">{{ old('description') }}</textarea>
            @error('description') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
        </div>
        
        <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('admin.demands.index') }}" 
               style="padding: 12px 24px; background: #f1f5f9; color: #64748b; text-decoration: none; border-radius: 8px; font-weight: 500;">
                Cancel
            </a>
            <button type="submit" 
                    style="padding: 12px 24px; background: #6366f1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Create Demand
            </button>
        </div>
    </form>
</div>
@endsection
