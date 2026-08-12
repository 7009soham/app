@extends('layouts.app')

@section('title', 'Design Exploration - Variation 3 - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@push('styles')
<link rel="stylesheet" href="{{ \App\Helpers\Asset::versioned('css/design-exploration.css') }}">
<style>
    .preview-v3 .hero-slider {
        min-height: 72vh;
        border-bottom: 1px solid #1f2f47;
    }

    .preview-v3 .slide-overlay {
        background: linear-gradient(180deg, rgba(7, 22, 41, 0.28), rgba(7, 22, 41, 0.82));
    }

    .preview-v3 .slide-content {
        max-width: 910px;
        text-align: center;
        padding: 0 var(--spacing-4);
    }

    .preview-v3 .slide-content h1 {
        font-size: clamp(2.1rem, 4.6vw, 3.5rem);
        letter-spacing: -0.01em;
    }

    .preview-v3 .slide-content p {
        max-width: 720px;
        margin-left: auto;
        margin-right: auto;
        color: rgba(255, 255, 255, 0.92);
    }

    .preview-v3 .stats-section {
        background: #0f2440;
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.09);
    }

    .preview-v3 .stat-card {
        background: #163055;
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: none;
    }

    .preview-v3 .stat-card i,
    .preview-v3 .stat-number {
        color: #fff;
    }

    .preview-v3 .stat-label {
        color: rgba(255, 255, 255, 0.8);
    }

    .preview-v3 .services-section {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    }

    .preview-v3 .service-card {
        border-radius: var(--radius-xl);
        border: 1px solid var(--color-gray-200);
        box-shadow: var(--shadow-sm);
    }

    .preview-v3 .service-card:hover {
        transform: translateY(-8px);
        border-color: #afc4e3;
        box-shadow: var(--shadow-xl);
    }

    .preview-v3 .service-icon {
        width: 78px;
        height: 78px;
        border-radius: 22px;
    }

    .preview-v3 .why-us-section {
        background: #f1f5f9;
        border-top: 1px solid var(--color-gray-200);
        border-bottom: 1px solid var(--color-gray-200);
    }

    .preview-v3 .feature-card {
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-xl);
        background: #fff;
        box-shadow: var(--shadow-sm);
    }

    .preview-v3 .feature-icon {
        background: #0f2440;
    }

    .preview-v3 .cta-section {
        background: linear-gradient(135deg, #0f2440 0%, #1a365d 55%, #244a78 100%);
    }

    .preview-v3 .cta-content {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-xl);
    }

    .preview-v3 .btn-outline-white {
        border-width: 2px;
    }
</style>
@endpush

@section('content')
<div class="preview-shell preview-v3">
    @include('design-exploration.home.partials.preview-toolbar', ['active' => 'v3'])

    @include('design-exploration.home.partials.original-home-sections')
</div>
@endsection

@push('scripts')
    @include('design-exploration.home.partials.original-home-scripts')
@endpush
