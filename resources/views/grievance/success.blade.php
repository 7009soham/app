@extends('layouts.app')

@section('title', 'Complaint Submitted Successfully')

@push('styles')
<style>
    .success-page {
        padding: 80px 0;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 80vh;
        display: flex;
        align-items: center;
    }

    .success-container {
        max-width: 600px;
        margin: 0 auto;
    }

    .success-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        overflow: hidden;
        text-align: center;
    }

    .success-header {
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        padding: 48px 32px;
        color: white;
    }

    .success-icon {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 48px;
        animation: scaleIn 0.5s ease;
    }

    @keyframes scaleIn {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .success-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .success-header p {
        font-size: 16px;
        opacity: 0.9;
    }

    .success-body {
        padding: 40px 32px;
    }

    .ticket-box {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px dashed #d97706;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 32px;
    }

    .ticket-label {
        font-size: 13px;
        font-weight: 600;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .ticket-number {
        font-size: 32px;
        font-weight: 800;
        color: #1e3a5f;
        font-family: monospace;
    }

    .ticket-hint {
        font-size: 14px;
        color: #92400e;
        margin-top: 12px;
    }

    .details-list {
        text-align: left;
        margin-bottom: 32px;
    }

    .details-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .details-item:last-child {
        border-bottom: none;
    }

    .details-label {
        color: #64748b;
        font-size: 14px;
    }

    .details-value {
        color: #1e3a5f;
        font-weight: 600;
        font-size: 14px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: #fef3c7;
        color: #d97706;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn {
        padding: 14px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.4);
    }

    .btn-outline {
        background: white;
        color: #1e3a5f;
        border: 2px solid #e5e7eb;
    }

    .btn-outline:hover {
        border-color: #1e3a5f;
        background: #f8fafc;
    }

    .info-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        padding: 16px;
        border-radius: 12px;
        margin-top: 24px;
        font-size: 14px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        text-align: left;
    }

    .info-note i {
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
<section class="success-page">
    <div class="container success-container">
        <div class="success-card">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Complaint Submitted!</h1>
                <p>Your grievance has been registered successfully</p>
            </div>

            <div class="success-body">
                <div class="ticket-box">
                    <div class="ticket-label">Your Ticket Number</div>
                    <div class="ticket-number">{{ $grievance->ticket_no }}</div>
                    <div class="ticket-hint">Save this number to track your complaint status</div>
                </div>

                <div class="details-list">
                    <div class="details-item">
                        <span class="details-label">Category</span>
                        <span class="details-value">{{ $grievance->category_label }}</span>
                    </div>
                    <div class="details-item">
                        <span class="details-label">Submitted On</span>
                        <span class="details-value">{{ $grievance->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="details-item">
                        <span class="details-label">Status</span>
                        <span class="status-badge">
                            <i class="fas fa-clock"></i>
                            Pending
                        </span>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('grievance.track') }}" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Track Status
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                </div>

                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>What happens next?</strong><br>
                        Our team will review your complaint and take necessary action. You can track the status using your ticket number. We typically respond within 24-48 hours.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
