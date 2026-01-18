@extends('layouts.app')

@section('title', 'Complaint Status - ' . $grievance->ticket_no)

@push('styles')
<style>
    .status-page {
        padding: 60px 0;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 80vh;
    }

    .status-container {
        max-width: 700px;
        margin: 0 auto;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 24px;
        transition: color 0.3s ease;
    }

    .back-link:hover {
        color: #1e3a5f;
    }

    .status-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .status-header {
        padding: 32px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }

    .ticket-info h1 {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }

    .ticket-info .ticket-no {
        font-size: 28px;
        font-weight: 800;
        color: #1e3a5f;
        font-family: monospace;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-badge.pending {
        background: #fef3c7;
        color: #d97706;
    }

    .status-badge.in_progress {
        background: #dbeafe;
        color: #2563eb;
    }

    .status-badge.resolved {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge.rejected {
        background: #fee2e2;
        color: #dc2626;
    }

    .status-body {
        padding: 32px;
    }

    /* Progress Timeline */
    .progress-timeline {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        position: relative;
    }

    .progress-timeline::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 40px;
        right: 40px;
        height: 4px;
        background: #e5e7eb;
        z-index: 0;
    }

    .progress-step {
        position: relative;
        text-align: center;
        flex: 1;
        z-index: 1;
    }

    .step-icon {
        width: 48px;
        height: 48px;
        background: #e5e7eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 18px;
        color: #9ca3af;
        transition: all 0.3s ease;
    }

    .progress-step.active .step-icon,
    .progress-step.completed .step-icon {
        background: #16a34a;
        color: white;
    }

    .progress-step.current .step-icon {
        background: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    }

    .step-label {
        font-size: 13px;
        font-weight: 600;
        color: #9ca3af;
    }

    .progress-step.active .step-label,
    .progress-step.completed .step-label,
    .progress-step.current .step-label {
        color: #1e3a5f;
    }

    /* Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    @media (max-width: 640px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-item {
        background: #f9fafb;
        border-radius: 12px;
        padding: 16px 20px;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 15px;
        font-weight: 600;
        color: #1e3a5f;
    }

    .description-text {
        font-size: 15px;
        color: #475569;
        line-height: 1.6;
    }

    /* Image Section */
    .image-section {
        margin-top: 24px;
    }

    .section-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .grievance-image {
        max-width: 100%;
        max-height: 300px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #e5e7eb;
    }

    /* Admin Remarks */
    .remarks-section {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 20px;
        margin-top: 24px;
    }

    .remarks-section h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1d4ed8;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .remarks-section p {
        color: #1e40af;
        font-size: 15px;
        line-height: 1.6;
    }

    /* Actions */
    .actions {
        display: flex;
        gap: 16px;
        margin-top: 32px;
        padding-top: 32px;
        border-top: 1px solid #f1f5f9;
    }

    .btn {
        padding: 14px 24px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .btn-outline {
        background: white;
        color: #1e3a5f;
        border: 2px solid #e5e7eb;
    }

    .btn-outline:hover {
        border-color: #1e3a5f;
    }
</style>
@endpush

@section('content')
<section class="status-page">
    <div class="container status-container">
        <a href="{{ route('grievance.track') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Track
        </a>

        <div class="status-card">
            <div class="status-header">
                <div class="ticket-info">
                    <h1>Ticket Number</h1>
                    <div class="ticket-no">{{ $grievance->ticket_no }}</div>
                </div>
                <span class="status-badge {{ $grievance->status }}">
                    @switch($grievance->status)
                        @case('pending')
                            <i class="fas fa-clock"></i> Pending
                            @break
                        @case('in_progress')
                            <i class="fas fa-spinner"></i> In Progress
                            @break
                        @case('resolved')
                            <i class="fas fa-check-circle"></i> Resolved
                            @break
                        @case('rejected')
                            <i class="fas fa-times-circle"></i> Rejected
                            @break
                    @endswitch
                </span>
            </div>

            <div class="status-body">
                <!-- Progress Timeline -->
                <div class="progress-timeline">
                    <div class="progress-step {{ in_array($grievance->status, ['pending', 'in_progress', 'resolved', 'rejected']) ? 'completed' : '' }}">
                        <div class="step-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="step-label">Submitted</div>
                    </div>
                    <div class="progress-step {{ $grievance->status === 'in_progress' ? 'current' : '' }} {{ $grievance->status === 'resolved' ? 'completed' : '' }}">
                        <div class="step-icon"><i class="fas fa-cog"></i></div>
                        <div class="step-label">In Progress</div>
                    </div>
                    <div class="progress-step {{ $grievance->status === 'resolved' ? 'completed' : '' }}">
                        <div class="step-icon"><i class="fas fa-check"></i></div>
                        <div class="step-label">Resolved</div>
                    </div>
                </div>

                <!-- Details -->
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value">{{ $grievance->category_label }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Submitted On</div>
                        <div class="detail-value">{{ $grievance->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">{{ $grievance->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">+91 {{ $grievance->phone }}</div>
                    </div>
                    @if($grievance->resolved_at)
                    <div class="detail-item full-width">
                        <div class="detail-label">Resolved On</div>
                        <div class="detail-value">{{ $grievance->resolved_at->format('d M Y, h:i A') }}</div>
                    </div>
                    @endif
                    <div class="detail-item full-width">
                        <div class="detail-label">Description</div>
                        <div class="description-text">{{ $grievance->description }}</div>
                    </div>
                </div>

                @if($grievance->image)
                <div class="image-section">
                    <div class="section-title">
                        <i class="fas fa-camera"></i> Attached Photo
                    </div>
                    <img src="{{ $grievance->image_url }}" alt="Grievance Image" class="grievance-image">
                </div>
                @endif

                @if($grievance->location_address)
                <div class="detail-item full-width" style="margin-top: 24px;">
                    <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Location</div>
                    <div class="detail-value">{{ $grievance->location_address }}</div>
                </div>
                @endif

                @if($grievance->admin_remarks)
                <div class="remarks-section">
                    <h4><i class="fas fa-comment-alt"></i> Admin Response</h4>
                    <p>{{ $grievance->admin_remarks }}</p>
                </div>
                @endif

                <div class="actions">
                    <a href="{{ route('grievance.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        New Complaint
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
