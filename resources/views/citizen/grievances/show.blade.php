@extends('citizen.layout')

@section('title', app()->getLocale() == 'hi' ? 'शिकायत विवरण' : (app()->getLocale() == 'mr' ? 'तक्रार तपशील' : 'Grievance Details'))
@section('page-title', app()->getLocale() == 'hi' ? 'शिकायत विवरण' : (app()->getLocale() == 'mr' ? 'तक्रार तपशील' : 'Grievance Details'))

@section('content')
<style>
    .detail-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 32px;
        box-shadow: var(--shadow);
        max-width: 900px;
        margin: 0 auto;
    }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
        gap: 16px;
    }

    .ticket-info h2 {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .ticket-date {
        color: var(--text-secondary);
        font-size: 14px;
    }

    .status-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-secondary { background: #f1f5f9; color: #475569; }

    .detail-section {
        margin-bottom: 32px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--primary);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
    }

    .info-value {
        font-size: 15px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .description-box {
        background: var(--surface-secondary);
        padding: 20px;
        border-radius: var(--radius);
        border-left: 4px solid var(--primary);
        line-height: 1.8;
        color: var(--text-primary);
    }

    .image-container {
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        max-width: 600px;
    }

    .image-container img {
        width: 100%;
        height: auto;
        display: block;
    }

    .map-container {
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
        height: 300px;
        background: var(--surface-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remarks-box {
        background: #fffbeb;
        border: 1px solid #fbbf24;
        padding: 20px;
        border-radius: var(--radius);
        margin-top: 16px;
    }

    .remarks-title {
        font-weight: 600;
        color: #92400e;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .remarks-text {
        color: #78350f;
        line-height: 1.6;
    }

    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--border);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -29px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary);
        border: 3px solid var(--surface);
        box-shadow: 0 0 0 2px var(--border);
    }

    .timeline-date {
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .timeline-content {
        background: var(--surface-secondary);
        padding: 12px 16px;
        border-radius: var(--radius);
        font-size: 14px;
        color: var(--text-primary);
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 24px;
        border-radius: var(--radius);
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
    }

    .btn-secondary {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-primary);
    }

    .btn-secondary:hover {
        background: var(--surface-secondary);
    }

    @media (max-width: 640px) {
        .detail-card {
            padding: 20px;
        }

        .detail-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="detail-card">
    <!-- Header -->
    <div class="detail-header">
        <div class="ticket-info">
            <h2>{{ $grievance->ticket_no }}</h2>
            <div class="ticket-date">
                <i class="fas fa-calendar"></i>
                {{ app()->getLocale() == 'hi' ? 'दर्ज किया गया: ' : (app()->getLocale() == 'mr' ? 'नोंदवले: ' : 'Submitted on: ') }}
                {{ $grievance->created_at->format('d M Y, h:i A') }}
            </div>
        </div>
        <div class="status-badges">
            <span class="badge badge-{{ $grievance->status_badge_class }}">
                {{ ucfirst(str_replace('_', ' ', $grievance->status)) }}
            </span>
            <span class="badge badge-{{ $grievance->priority_badge_class }}">
                {{ ucfirst($grievance->priority) }} Priority
            </span>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="detail-section">
        <h3 class="section-title">
            <i class="fas fa-info-circle"></i>
            {{ app()->getLocale() == 'hi' ? 'मूल जानकारी' : (app()->getLocale() == 'mr' ? 'मूलभूत माहिती' : 'Basic Information') }}
        </h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">
                    {{ app()->getLocale() == 'hi' ? 'श्रेणी' : (app()->getLocale() == 'mr' ? 'श्रेणी' : 'Category') }}
                </div>
                <div class="info-value">
                    <i class="fas fa-tag" style="color: var(--primary); margin-right: 6px;"></i>
                    {{ $grievance->category_label }}
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">
                    {{ app()->getLocale() == 'hi' ? 'स्थिति' : (app()->getLocale() == 'mr' ? 'स्थिती' : 'Status') }}
                </div>
                <div class="info-value">
                    {{ ucfirst(str_replace('_', ' ', $grievance->status)) }}
                </div>
            </div>
            @if($grievance->resolved_at)
            <div class="info-item">
                <div class="info-label">
                    {{ app()->getLocale() == 'hi' ? 'हल किया गया' : (app()->getLocale() == 'mr' ? 'सोडवले' : 'Resolved On') }}
                </div>
                <div class="info-value">
                    {{ $grievance->resolved_at->format('d M Y, h:i A') }}
                </div>
            </div>
            @endif
            @if($grievance->assignedAdmin)
            <div class="info-item">
                <div class="info-label">
                    {{ app()->getLocale() == 'hi' ? 'सौंपा गया' : (app()->getLocale() == 'mr' ? 'नियुक्त केले' : 'Assigned To') }}
                </div>
                <div class="info-value">
                    {{ $grievance->assignedAdmin->name }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Description -->
    <div class="detail-section">
        <h3 class="section-title">
            <i class="fas fa-file-alt"></i>
            {{ app()->getLocale() == 'hi' ? 'विवरण' : (app()->getLocale() == 'mr' ? 'तपशील' : 'Description') }}
        </h3>
        <div class="description-box">
            {{ $grievance->description }}
        </div>
    </div>

    <!-- Image -->
    @if($grievance->image)
    <div class="detail-section">
        <h3 class="section-title">
            <i class="fas fa-image"></i>
            {{ app()->getLocale() == 'hi' ? 'फोटो' : (app()->getLocale() == 'mr' ? 'फोटो' : 'Photo') }}
        </h3>
        <div class="image-container">
            <img src="{{ $grievance->image_url }}" alt="Grievance Image">
        </div>
    </div>
    @endif

    <!-- Location -->
    @if($grievance->location_address || ($grievance->latitude && $grievance->longitude))
    <div class="detail-section">
        <h3 class="section-title">
            <i class="fas fa-map-marker-alt"></i>
            {{ app()->getLocale() == 'hi' ? 'स्थान' : (app()->getLocale() == 'mr' ? 'स्थान' : 'Location') }}
        </h3>
        @if($grievance->location_address)
        <div class="info-value" style="margin-bottom: 16px;">
            {{ $grievance->location_address }}
        </div>
        @endif
        @if($grievance->latitude && $grievance->longitude)
        <div class="map-container">
            <iframe 
                width="100%" 
                height="100%" 
                frameborder="0" 
                style="border:0" 
                src="https://www.google.com/maps?q={{ $grievance->latitude }},{{ $grievance->longitude }}&output=embed"
                allowfullscreen>
            </iframe>
        </div>
        @endif
    </div>
    @endif

    <!-- Admin Remarks -->
    @if($grievance->admin_remarks)
    <div class="detail-section">
        <h3 class="section-title">
            <i class="fas fa-comment-alt"></i>
            {{ app()->getLocale() == 'hi' ? 'प्रशासनिक टिप्पणियां' : (app()->getLocale() == 'mr' ? 'प्रशासकीय टिप्पण्या' : 'Admin Remarks') }}
        </h3>
        <div class="remarks-box">
            <div class="remarks-title">
                <i class="fas fa-user-shield"></i>
                {{ app()->getLocale() == 'hi' ? 'ग्राम पंचायत से जवाब' : (app()->getLocale() == 'mr' ? 'ग्राम पंचायतचे उत्तर' : 'Response from Gram Panchayat') }}
            </div>
            <div class="remarks-text">
                {{ $grievance->admin_remarks }}
            </div>
        </div>
    </div>
    @endif

    <!-- Timeline -->
    <div class="detail-section">
        <h3 class="section-title">
            <i class="fas fa-history"></i>
            {{ app()->getLocale() == 'hi' ? 'गतिविधि समयरेखा' : (app()->getLocale() == 'mr' ? 'क्रियाकलाप टाइमलाइन' : 'Activity Timeline') }}
        </h3>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">{{ $grievance->created_at->format('d M Y, h:i A') }}</div>
                <div class="timeline-content">
                    <i class="fas fa-plus-circle" style="color: var(--accent);"></i>
                    {{ app()->getLocale() == 'hi' ? 'शिकायत दर्ज की गई' : (app()->getLocale() == 'mr' ? 'तक्रार नोंदवली' : 'Grievance submitted') }}
                </div>
            </div>
            @if($grievance->status == 'in_progress' || $grievance->status == 'resolved')
            <div class="timeline-item">
                <div class="timeline-date">{{ $grievance->updated_at->format('d M Y, h:i A') }}</div>
                <div class="timeline-content">
                    <i class="fas fa-cog" style="color: var(--secondary);"></i>
                    {{ app()->getLocale() == 'hi' ? 'प्रक्रिया में है' : (app()->getLocale() == 'mr' ? 'प्रक्रियेत आहे' : 'In Progress') }}
                </div>
            </div>
            @endif
            @if($grievance->status == 'resolved' && $grievance->resolved_at)
            <div class="timeline-item">
                <div class="timeline-date">{{ $grievance->resolved_at->format('d M Y, h:i A') }}</div>
                <div class="timeline-content">
                    <i class="fas fa-check-circle" style="color: var(--accent);"></i>
                    {{ app()->getLocale() == 'hi' ? 'शिकायत हल हो गई' : (app()->getLocale() == 'mr' ? 'तक्रार सोडवली' : 'Grievance resolved') }}
                </div>
            </div>
            @endif
            @if($grievance->status == 'rejected')
            <div class="timeline-item">
                <div class="timeline-date">{{ $grievance->updated_at->format('d M Y, h:i A') }}</div>
                <div class="timeline-content">
                    <i class="fas fa-times-circle" style="color: #dc2626;"></i>
                    {{ app()->getLocale() == 'hi' ? 'शिकायत अस्वीकार कर दी गई' : (app()->getLocale() == 'mr' ? 'तक्रार नाकारली' : 'Grievance rejected') }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('citizen.grievances.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            {{ app()->getLocale() == 'hi' ? 'वापस जाएं' : (app()->getLocale() == 'mr' ? 'परत जा' : 'Back to List') }}
        </a>
        @if($grievance->status == 'pending')
        <button class="btn btn-primary" onclick="alert('{{ app()->getLocale() == 'hi' ? 'शिकायत प्रक्रिया में है। कृपया धैर्य रखें।' : (app()->getLocale() == 'mr' ? 'तक्रार प्रक्रियेत आहे. कृपया धीर धरा.' : 'Your grievance is being processed. Please be patient.') }}')">
            <i class="fas fa-clock"></i>
            {{ app()->getLocale() == 'hi' ? 'प्रतीक्षा कर रहे हैं' : (app()->getLocale() == 'mr' ? 'प्रतीक्षा करीत आहे' : 'Awaiting Response') }}
        </button>
        @endif
    </div>
</div>
@endsection
