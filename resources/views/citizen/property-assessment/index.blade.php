@extends('citizen.layout')

@section('title', app()->getLocale() == 'mr' ? 'मूल्यांकन यादी' : (app()->getLocale() == 'hi' ? 'संपत्ति मूल्यांकन' : 'Property Assessment'))

@section('page-title', app()->getLocale() == 'mr' ? 'मूल्यांकन यादी' : (app()->getLocale() == 'hi' ? 'संपत्ति मूल्यांकन' : 'Property Assessment'))

@section('content')
<style>
    .assessment-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 50%, #10b981 100%);
        border-radius: 16px;
        padding: 32px;
        color: white;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .assessment-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .assessment-hero h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .assessment-hero p {
        font-size: 14px;
        opacity: 0.9;
    }

    .hero-stats {
        display: flex;
        gap: 24px;
        margin-top: 20px;
    }

    .hero-stat {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        padding: 16px 24px;
        border-radius: 12px;
        min-width: 160px;
    }

    .hero-stat .stat-label {
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 4px;
    }

    .hero-stat .stat-value {
        font-size: 22px;
        font-weight: 700;
    }

    .info-banner {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-banner.info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .info-banner i {
        font-size: 20px;
        margin-top: 2px;
    }

    .info-banner .info-text h4 {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-banner .info-text p {
        font-size: 13px;
        margin: 0;
    }

    .assessment-grid {
        display: grid;
        gap: 20px;
    }

    .assessment-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    .assessment-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }

    .card-header-section {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .property-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1e3a5f, #2d5a8e);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .property-details h3 {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .property-details .sub-info {
        font-size: 13px;
        color: #64748b;
    }

    .assessment-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .assessment-type-badge.regular { background: #dcfce7; color: #16a34a; }
    .assessment-type-badge.rented { background: #dbeafe; color: #2563eb; }
    .assessment-type-badge.extended { background: #ffedd5; color: #ea580c; }

    .card-body-section {
        padding: 20px 24px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .detail-item {
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 10px;
    }

    .detail-item .label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .detail-item .value {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
    }

    .card-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-print {
        background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        color: white;
    }

    .btn-print:hover {
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
        transform: translateY(-1px);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }

    .empty-state i {
        font-size: 56px;
        color: #cbd5e1;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 14px;
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .hero-stats {
            flex-direction: column;
            gap: 12px;
        }

        .card-header-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .detail-grid {
            grid-template-columns: 1fr 1fr;
        }

        .card-actions {
            flex-direction: column;
        }

        .btn-action {
            justify-content: center;
        }
    }
</style>

<!-- Hero Section -->
<div class="assessment-hero">
    <h2><i class="fas fa-file-alt"></i> {{ app()->getLocale() == 'mr' ? 'मूल्यांकन यादी (फॉर्म नं. ८)' : (app()->getLocale() == 'hi' ? 'संपत्ति मूल्यांकन (फॉर्म नं. 8)' : 'Property Assessment (Form No. 8)') }}</h2>
    <p>{{ app()->getLocale() == 'mr' ? 'तुमच्या मालमत्तेची मूल्यांकन यादी पाहा आणि प्रिंट करा' : (app()->getLocale() == 'hi' ? 'अपनी संपत्ति का मूल्यांकन देखें और प्रिंट करें' : 'View and print your property assessment records') }}</p>

    <div class="hero-stats">
        <div class="hero-stat">
            <div class="stat-label">{{ app()->getLocale() == 'mr' ? 'एकूण मूल्यांकनें' : (app()->getLocale() == 'hi' ? 'कुल मूल्यांकन' : 'Total Assessments') }}</div>
            <div class="stat-value">{{ $assessments->count() }}</div>
        </div>
        <div class="hero-stat">
            <div class="stat-label">{{ app()->getLocale() == 'mr' ? 'एकूण मालमत्ता' : (app()->getLocale() == 'hi' ? 'कुल संपत्ति' : 'Total Properties') }}</div>
            <div class="stat-value">{{ $propertyTaxRecords->count() }}</div>
        </div>
    </div>
</div>

<!-- Info Banner -->
<div class="info-banner info">
    <i class="fas fa-info-circle"></i>
    <div class="info-text">
        <h4>{{ app()->getLocale() == 'mr' ? 'मूल्यांकन प्रत' : (app()->getLocale() == 'hi' ? 'मूल्यांकन प्रति' : 'Assessment Copy') }}</h4>
        <p>{{ app()->getLocale() == 'mr' ? 'या प्रतवर "सरकारी वापरासाठी नाही" वॉटरमार्क असेल. अधिकृत प्रत मिळविण्यासाठी कृपया ग्रामपंचायत कार्यालयाशी संपर्क साधा.' : (app()->getLocale() == 'hi' ? 'इस प्रति पर "सरकारी उपयोग के लिए नहीं" वॉटरमार्क होगा। आधिकारिक प्रति के लिए कृपया ग्रामपंचायत कार्यालय से संपर्क करें।' : 'These copies will have a "NOT FOR OFFICIAL USE" watermark. For an official copy, please contact the Gram Panchayat office.') }}</p>
    </div>
</div>

<!-- Assessment Cards -->
<div class="assessment-grid">
    @forelse($assessments as $assessment)
    <div class="assessment-card">
        <div class="card-header-section">
            <div class="card-header-left">
                <div class="property-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="property-details">
                    <h3>{{ app()->getLocale() == 'mr' ? 'मालमत्ता क्र.' : (app()->getLocale() == 'hi' ? 'संपत्ति क्र.' : 'Property #') }} {{ $assessment->property_number }}</h3>
                    <div class="sub-info">
                        {{ $assessment->owner_name }} &bull;
                        {{ app()->getLocale() == 'mr' ? 'आ.वर्ष' : (app()->getLocale() == 'hi' ? 'वि.वर्ष' : 'F.Y.') }}: {{ $assessment->financial_year }}
                    </div>
                </div>
            </div>
            <div>
                <span class="assessment-type-badge {{ $assessment->assessment_type }}">
                    @if($assessment->assessment_type === 'regular')
                        <i class="fas fa-check-circle"></i>
                    @elseif($assessment->assessment_type === 'rented')
                        <i class="fas fa-key"></i>
                    @else
                        <i class="fas fa-expand-arrows-alt"></i>
                    @endif
                    {{ $assessment->type_marathi }} ({{ ucfirst($assessment->assessment_type) }})
                </span>
            </div>
        </div>
        <div class="card-body-section">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label">{{ app()->getLocale() == 'mr' ? 'चौ.फूट' : (app()->getLocale() == 'hi' ? 'वर्ग फुट' : 'Sq. Ft.') }}</div>
                    <div class="value">{{ $assessment->square_foot ? number_format($assessment->square_foot, 0) : '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">{{ app()->getLocale() == 'mr' ? 'भांडवली मूल्य' : (app()->getLocale() == 'hi' ? 'पूंजी मूल्य' : 'Capital Value') }}</div>
                    <div class="value">{{ $assessment->capital_value ? '₹' . number_format($assessment->capital_value, 0) : '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">{{ app()->getLocale() == 'mr' ? 'घर कर' : (app()->getLocale() == 'hi' ? 'गृह कर' : 'House Tax') }}</div>
                    <div class="value">{{ $assessment->house_tax ? '₹' . number_format($assessment->house_tax, 0) : '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">{{ app()->getLocale() == 'mr' ? 'एकूण' : (app()->getLocale() == 'hi' ? 'कुल' : 'Total') }}</div>
                    <div class="value" style="color: #16a34a;">{{ $assessment->grand_total ? '₹' . number_format($assessment->grand_total, 0) : '-' }}</div>
                </div>
            </div>

            <div class="card-actions">
                <a href="{{ route('citizen.property-assessment.print', $assessment->id) }}" target="_blank" class="btn-action btn-print">
                    <i class="fas fa-print"></i>
                    {{ app()->getLocale() == 'mr' ? 'प्रिंट करा' : (app()->getLocale() == 'hi' ? 'प्रिंट करें' : 'Print Assessment') }}
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-file-alt"></i>
        <h3>{{ app()->getLocale() == 'mr' ? 'कोणतेही मूल्यांकन सापडले नाही' : (app()->getLocale() == 'hi' ? 'कोई मूल्यांकन नहीं मिला' : 'No Assessments Found') }}</h3>
        <p>{{ app()->getLocale() == 'mr' ? 'तुमच्या मालमत्तेसाठी अद्याप कोणतेही मूल्यांकन उपलब्ध नाही.' : (app()->getLocale() == 'hi' ? 'आपकी संपत्ति के लिए अभी तक कोई मूल्यांकन उपलब्ध नहीं है।' : 'No property assessments are available for your properties yet.') }}</p>
    </div>
    @endforelse
</div>
@endsection
