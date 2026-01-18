@extends('admin.layouts.app')

@section('title', 'Grievance Details - ' . $grievance->ticket_no)

@section('page-title', 'Grievance Details')

@push('styles')
<style>
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 24px;
    }

    .back-link:hover {
        color: #1e3a5f;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 992px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .main-card,
    .side-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .card-body {
        padding: 24px;
    }

    /* Ticket Header */
    .ticket-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .ticket-no {
        font-size: 28px;
        font-weight: 800;
        color: #1e3a5f;
        font-family: monospace;
    }

    .ticket-date {
        font-size: 14px;
        color: #64748b;
        margin-top: 4px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-badge.pending { background: #fef3c7; color: #d97706; }
    .status-badge.in_progress { background: #dbeafe; color: #2563eb; }
    .status-badge.resolved { background: #dcfce7; color: #16a34a; }
    .status-badge.rejected { background: #fee2e2; color: #dc2626; }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 576px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-item {
        background: #f8fafc;
        border-radius: 10px;
        padding: 16px;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
    }

    .info-value a {
        color: #2563eb;
        text-decoration: none;
    }

    /* Description */
    .description-section {
        background: #f8fafc;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .description-text {
        font-size: 15px;
        color: #475569;
        line-height: 1.7;
        white-space: pre-wrap;
    }

    /* Image */
    .image-section {
        margin-bottom: 24px;
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
        border-radius: 12px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .grievance-image:hover {
        transform: scale(1.02);
    }

    /* Location */
    .location-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px;
    }

    .location-info i {
        color: #2563eb;
        margin-right: 8px;
    }

    /* Side Card - Status Update */
    .status-form {
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-select,
    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        resize: vertical;
    }

    .form-textarea {
        min-height: 100px;
    }

    .update-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .update-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.3);
    }

    /* Category & Priority */
    .category-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f1f5f9;
        color: #475569;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
    }

    .priority-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-badge.low { background: #f1f5f9; color: #64748b; }
    .priority-badge.medium { background: #dbeafe; color: #2563eb; }
    .priority-badge.high { background: #fef3c7; color: #d97706; }
    .priority-badge.urgent { background: #fee2e2; color: #dc2626; }

    /* Danger Zone */
    .danger-zone {
        border-top: 1px solid #fee2e2;
        padding-top: 20px;
        margin-top: 20px;
    }

    .danger-title {
        font-size: 13px;
        font-weight: 600;
        color: #dc2626;
        margin-bottom: 12px;
    }

    .delete-btn {
        width: 100%;
        padding: 10px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .delete-btn:hover {
        background: #fecaca;
    }

    /* Alert */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-success {
        background: #dcfce7;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
</style>
@endpush

@section('content')
<a href="{{ route('admin.grievances.index') }}" class="back-link">
    <i class="fas fa-arrow-left"></i>
    Back to Grievances
</a>

@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="detail-grid">
    <!-- Main Content -->
    <div class="main-card">
        <div class="card-body">
            <div class="ticket-header">
                <div>
                    <div class="ticket-no">{{ $grievance->ticket_no }}</div>
                    <div class="ticket-date">Submitted on {{ $grievance->created_at->format('d M Y, h:i A') }}</div>
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

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Complainant Name</div>
                    <div class="info-value">{{ $grievance->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">
                        <a href="tel:+91{{ $grievance->phone }}">+91 {{ $grievance->phone }}</a>
                    </div>
                </div>
                @if($grievance->email)
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <a href="mailto:{{ $grievance->email }}">{{ $grievance->email }}</a>
                    </div>
                </div>
                @endif
                @if($grievance->address)
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $grievance->address }}</div>
                </div>
                @endif
                <div class="info-item">
                    <div class="info-label">Category</div>
                    <div class="info-value">
                        <span class="category-badge">{{ $grievance->category_label }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Priority</div>
                    <div class="info-value">
                        <span class="priority-badge {{ $grievance->priority }}">
                            {{ ucfirst($grievance->priority) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="description-section">
                <div class="info-label" style="margin-bottom: 12px;">Problem Description</div>
                <div class="description-text">{{ $grievance->description }}</div>
            </div>

            @if($grievance->image)
            <div class="image-section">
                <div class="section-title">
                    <i class="fas fa-camera"></i> Attached Photo
                </div>
                <a href="{{ $grievance->image_url }}" target="_blank">
                    <img src="{{ $grievance->image_url }}" alt="Grievance Image" class="grievance-image">
                </a>
            </div>
            @endif

            @if($grievance->location_address || ($grievance->latitude && $grievance->longitude))
            <div class="location-info">
                <i class="fas fa-map-marker-alt"></i>
                @if($grievance->location_address)
                    {{ $grievance->location_address }}
                @else
                    Lat: {{ $grievance->latitude }}, Lng: {{ $grievance->longitude }}
                @endif
                @if($grievance->latitude && $grievance->longitude)
                <br>
                <a href="https://www.google.com/maps?q={{ $grievance->latitude }},{{ $grievance->longitude }}" 
                   target="_blank" style="font-size: 13px;">
                    View on Google Maps →
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Side Panel -->
    <div class="side-card">
        <div class="card-header">
            <h3 class="card-title">Update Status</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.grievances.update-status', $grievance) }}" method="POST" class="status-form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending" {{ $grievance->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $grievance->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $grievance->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="rejected" {{ $grievance->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low" {{ $grievance->priority == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ $grievance->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ $grievance->priority == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ $grievance->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Admin Remarks</label>
                    <textarea name="admin_remarks" class="form-textarea" 
                              placeholder="Add notes or response for the citizen...">{{ $grievance->admin_remarks }}</textarea>
                </div>

                <button type="submit" class="update-btn">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </form>

            @if($grievance->resolved_at)
            <div class="info-item" style="margin-bottom: 20px;">
                <div class="info-label">Resolved On</div>
                <div class="info-value">{{ $grievance->resolved_at->format('d M Y, h:i A') }}</div>
            </div>
            @endif

            <div class="danger-zone">
                <div class="danger-title">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </div>
                <form action="{{ route('admin.grievances.destroy', $grievance) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this grievance? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn">
                        <i class="fas fa-trash"></i> Delete Grievance
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
