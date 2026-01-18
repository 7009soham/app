@extends('layouts.app')

@section('title', 'Track Your Complaint')

@push('styles')
<style>
    .track-page {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 80vh;
    }

    .track-container {
        max-width: 500px;
        margin: 0 auto;
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 36px;
        font-weight: 700;
        color: #1e3a5f;
        margin-bottom: 12px;
    }

    .page-header p {
        font-size: 16px;
        color: #64748b;
    }

    .track-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        padding: 40px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
    }

    .form-input {
        width: 100%;
        padding: 16px 20px;
        font-size: 18px;
        font-family: monospace;
        letter-spacing: 2px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: #f9fafb;
        text-align: center;
        text-transform: uppercase;
        transition: all 0.3s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: #ef4444;
        background: white;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .form-input::placeholder {
        text-transform: none;
        letter-spacing: normal;
        font-family: inherit;
        color: #9ca3af;
    }

    .submit-btn {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 32px 0;
        color: #9ca3af;
        font-size: 14px;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }

    .new-complaint-link {
        display: block;
        text-align: center;
        padding: 16px;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        color: #1e3a5f;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .new-complaint-link:hover {
        border-color: #1e3a5f;
        background: white;
    }

    .new-complaint-link i {
        margin-right: 8px;
    }

    /* Alert */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .alert i {
        font-size: 20px;
    }
</style>
@endpush

@section('content')
<section class="track-page">
    <div class="container track-container">
        <div class="page-header">
            <h1><i class="fas fa-search"></i> Track Complaint</h1>
            <p>Enter your ticket number to check the status</p>
        </div>

        <div class="track-card">
            @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('grievance.status') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Ticket Number</label>
                    <input type="text" name="ticket_no" class="form-input" 
                           placeholder="e.g., GRV-2026-0001" required
                           value="{{ old('ticket_no') }}">
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-search"></i>
                    Track Status
                </button>
            </form>

            <div class="divider">or</div>

            <a href="{{ route('grievance.create') }}" class="new-complaint-link">
                <i class="fas fa-plus-circle"></i>
                Submit New Complaint
            </a>
        </div>
    </div>
</section>
@endsection
