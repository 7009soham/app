@extends('admin.layouts.app')

@section('title', 'Add Citizen')

@section('content')
<div class="page-header" style="margin-bottom: 24px;">
    <h1 class="page-title" style="font-size: 24px; font-weight: 700; color: #1e293b;">
        <a href="{{ route('admin.citizens.index') }}" style="color: #64748b; margin-right: 10px; font-size: 20px;"><i class="fas fa-arrow-left"></i></a>
        Add New Citizen
    </h1>
</div>

<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 32px; max-width: 800px;">
    <form action="{{ route('admin.citizens.store') }}" method="POST">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                @error('name') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                @error('phone') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                @error('email') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Customer Number</label>
                <input type="text" name="customer_no" value="{{ old('customer_no') }}" required
                       style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                @error('customer_no') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Aadhar Card (12 digits)</label>
                <input type="text" name="aadhar_card" value="{{ old('aadhar_card') }}" maxlength="12"
                       style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
                @error('aadhar_card') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Demand</label>
                <select name="demand_id" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; background: white;">
                    <option value="">Select Demand</option>
                    @foreach($demands as $demand)
                        <option value="{{ $demand->id }}" {{ old('demand_id') == $demand->id ? 'selected' : '' }}>
                            {{ $demand->name }}
                        </option>
                    @endforeach
                </select>
                @error('demand_id') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group" style="margin-bottom: 20px; grid-column: span 2;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #64748b;">Address</label>
                <textarea name="address" rows="3"
                          style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">{{ old('address') }}</textarea>
                @error('address') <span style="color: red; font-size: 13px;">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('admin.citizens.index') }}" 
               style="padding: 12px 24px; background: #f1f5f9; color: #64748b; text-decoration: none; border-radius: 8px; font-weight: 500;">
                Cancel
            </a>
            <button type="submit" 
                    style="padding: 12px 24px; background: #6366f1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Create Citizen
            </button>
        </div>
    </form>
</div>
@endsection
