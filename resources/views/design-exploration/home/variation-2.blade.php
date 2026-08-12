@extends('layouts.app')

@section('title', 'Design Exploration - Variation 2 - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@push('styles')
<link rel="stylesheet" href="{{ \App\Helpers\Asset::versioned('css/design-exploration.css') }}">
<style>
    .preview-v2 .hero-slider {
        min-height: 68vh;
    }

    .preview-v2 .slide-overlay {
        background: radial-gradient(circle at 15% 30%, rgba(249, 115, 22, 0.26), transparent 44%), linear-gradient(108deg, rgba(10, 29, 55, 0.83) 14%, rgba(13, 39, 74, 0.62) 46%, rgba(10, 29, 55, 0.35) 100%);
    }

    .preview-v2 .slide-content {
        max-width: 760px;
    }

    .preview-v2 .slide-content h1 {
        font-size: clamp(2rem, 4.4vw, 3.2rem);
    }

    .preview-v2 .slider-dots .dot {
        width: 34px;
        height: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.35);
    }

    .preview-v2 .slider-dots .dot.active {
        background: #fff;
    }

    .preview-v2 .stats-section {
        padding-top: var(--spacing-16);
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border-bottom: 1px solid var(--color-gray-200);
    }

    .preview-v2 .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: var(--spacing-5);
    }

    .preview-v2 .stat-card {
        border-radius: var(--radius-xl);
        border: 1px solid #cfd9e7;
        box-shadow: var(--shadow-sm);
    }

    .preview-v2 .services-section {
        padding-top: var(--spacing-20);
        position: relative;
    }

    .preview-v2 .services-section::before {
        content: '';
        position: absolute;
        top: 44px;
        left: 50%;
        transform: translateX(-50%);
        width: min(940px, calc(100% - 2rem));
        height: 1px;
        background: linear-gradient(90deg, transparent, #cad8ea, transparent);
    }

    .preview-v2 .services-grid {
        gap: var(--spacing-6);
    }

    .preview-v2 .service-card {
        border-radius: var(--radius-xl);
        border: 1px solid var(--color-gray-200);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-8) var(--spacing-6);
    }

    .preview-v2 .service-card .btn {
        width: 100%;
    }

    .preview-v2 .why-us-section {
        background: #f8fafc;
        border-top: 1px solid var(--color-gray-200);
        border-bottom: 1px solid var(--color-gray-200);
    }

    .preview-v2 .features-grid {
        gap: var(--spacing-5);
    }

    .preview-v2 .feature-card {
        border-radius: var(--radius-xl);
        border: 1px solid var(--color-gray-200);
        background: var(--color-white);
        box-shadow: var(--shadow-sm);
    }

    .preview-v2 .cta-content {
        border-radius: var(--radius-xl);
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow: 0 18px 36px rgba(15, 36, 64, 0.22);
    }

    @media (max-width: 768px) {
        .preview-v2 .stats-grid {
            grid-template-columns: 1fr;
        }

        .preview-v2 .slide-content {
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
<div class="preview-shell preview-v2">
    @include('design-exploration.home.partials.preview-toolbar', ['active' => 'v2'])

    @include('design-exploration.home.partials.original-home-sections')
</div>
@endsection

@push('scripts')
    @include('design-exploration.home.partials.original-home-scripts')
@endpush
