@extends('citizen.layout')

@section('title', app()->getLocale() == 'hi' ? 'मेरी शिकायतें' : (app()->getLocale() == 'mr' ? 'माझ्या तक्रारी' : 'My Grievances'))
@section('page-title', app()->getLocale() == 'hi' ? 'मेरी शिकायतें' : (app()->getLocale() == 'mr' ? 'माझ्या तक्रारी' : 'My Grievances'))

@section('content')
<style>
    .grievances-grid {
        display: grid;
        gap: 20px;
    }

    .grievance-card {
        background: var(--surface);
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 1px solid var(--border);
    }

    .grievance-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .grievance-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .grievance-ticket {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
    }

    .grievance-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-secondary { background: #f1f5f9; color: #475569; }

    .grievance-category {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .grievance-description {
        color: var(--text-primary);
        margin-bottom: 16px;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .grievance-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 16px;
        flex-wrap: wrap;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .grievance-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .grievance-actions {
        display: flex;
        gap: 12px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .btn {
        padding: 10px 20px;
        border-radius: var(--radius);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text-primary);
    }

    .btn-outline:hover {
        background: var(--surface-secondary);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--surface);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .empty-state-icon {
        font-size: 64px;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    .empty-state-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 12px;
    }

    .empty-state-text {
        font-size: 16px;
        color: var(--text-secondary);
        margin-bottom: 24px;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 32px;
    }
</style>

<div class="header-section">
    <div>
        <h2 style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">
            {{ app()->getLocale() == 'hi' ? 'मेरी शिकायतें' : (app()->getLocale() == 'mr' ? 'माझ्या तक्रारी' : 'My Grievances') }}
        </h2>
        <p style="color: var(--text-secondary);">
            {{ app()->getLocale() == 'hi' ? 'अपनी सभी शिकायतों को ट्रैक और प्रबंधित करें' : (app()->getLocale() == 'mr' ? 'तुमच्या सर्व तक्रारी ट्रॅक आणि व्यवस्थापित करा' : 'Track and manage all your grievances') }}
        </p>
    </div>
    <a href="{{ route('citizen.grievances.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        {{ app()->getLocale() == 'hi' ? 'नई शिकायत दर्ज करें' : (app()->getLocale() == 'mr' ? 'नवीन तक्रार नोंदवा' : 'Submit New Grievance') }}
    </a>
</div>

@if($grievances->count() > 0)
    <div class="grievances-grid">
        @foreach($grievances as $grievance)
            <div class="grievance-card">
                <div class="grievance-header">
                    <div class="grievance-ticket">{{ $grievance->ticket_no }}</div>
                    <div class="grievance-badges">
                        <span class="badge badge-{{ $grievance->status_badge_class }}">
                            {{ ucfirst(str_replace('_', ' ', $grievance->status)) }}
                        </span>
                        <span class="badge badge-{{ $grievance->priority_badge_class }}">
                            {{ ucfirst($grievance->priority) }}
                        </span>
                    </div>
                </div>

                <div class="grievance-category">
                    <i class="fas fa-tag"></i>
                    <span>{{ $grievance->category_label }}</span>
                </div>

                <p class="grievance-description">{{ $grievance->description }}</p>

                <div class="grievance-meta">
                    <div class="grievance-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>{{ $grievance->created_at->format('d M Y') }}</span>
                    </div>
                    @if($grievance->location_address)
                        <div class="grievance-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ Str::limit($grievance->location_address, 30) }}</span>
                        </div>
                    @endif
                </div>

                <div class="grievance-actions">
                    <a href="{{ route('citizen.grievances.show', $grievance->id) }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                        {{ app()->getLocale() == 'hi' ? 'विवरण देखें' : (app()->getLocale() == 'mr' ? 'तपशील पहा' : 'View Details') }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pagination">
        {{ $grievances->links() }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h3 class="empty-state-title">
            {{ app()->getLocale() == 'hi' ? 'कोई शिकायत नहीं' : (app()->getLocale() == 'mr' ? 'कोणतीही तक्रार नाही' : 'No Grievances Yet') }}
        </h3>
        <p class="empty-state-text">
            {{ app()->getLocale() == 'hi' ? 'आपने अभी तक कोई शिकायत दर्ज नहीं की है।' : (app()->getLocale() == 'mr' ? 'तुम्ही अद्याप कोणतीही तक्रार नोंदवली नाही.' : 'You haven\'t submitted any grievances yet.') }}
        </p>
        <a href="{{ route('citizen.grievances.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            {{ app()->getLocale() == 'hi' ? 'पहली शिकायत दर्ज करें' : (app()->getLocale() == 'mr' ? 'पहिली तक्रार नोंदवा' : 'Submit Your First Grievance') }}
        </a>
    </div>
@endif
@endsection
