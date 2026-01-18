@extends('admin.layouts.app')

@section('title', 'Edit Water Tax Record')

@push('styles')
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
        color: #1e3a5f;
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
        max-width: 800px;
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
        border-color: #1e3a5f;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
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
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
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
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <a href="{{ route('admin.water-tax.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Water Tax
    </a>
    <h1 class="page-title">Edit Water Tax Record</h1>
</div>

<form action="{{ route('admin.water-tax.update', $waterTaxRecord) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="form-card">
        <div class="form-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">A.No *</label>
                    <input type="number" name="a_no" class="form-input" value="{{ old('a_no', $waterTaxRecord->a_no) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Customer No *</label>
                    <input type="text" name="customer_no" class="form-input" value="{{ old('customer_no', $waterTaxRecord->customer_no) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" name="customer_name" class="form-input" value="{{ old('customer_name', $waterTaxRecord->customer_name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone', $waterTaxRecord->phone) }}" maxlength="10">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Monthly Bill *</label>
                    <input type="number" name="monthly_bill" class="form-input" value="{{ old('monthly_bill', $waterTaxRecord->monthly_bill) }}" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Period</label>
                    <input type="text" name="period" class="form-input" value="{{ old('period', $waterTaxRecord->period) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Balance Due *</label>
                    <input type="number" name="balance" class="form-input" value="{{ old('balance', $waterTaxRecord->balance) }}" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" name="amount_paid" class="form-input" value="{{ old('amount_paid', $waterTaxRecord->amount_paid) }}" step="0.01">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Link to Citizen (Optional)</label>
                <select name="citizen_id" class="form-select">
                    <option value="">-- Not Linked --</option>
                    @foreach($citizens as $citizen)
                    <option value="{{ $citizen->id }}" {{ old('citizen_id', $waterTaxRecord->citizen_id) == $citizen->id ? 'selected' : '' }}>
                        {{ $citizen->name }} ({{ $citizen->phone }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.water-tax.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Update Record
            </button>
        </div>
    </div>
</form>
@endsection
