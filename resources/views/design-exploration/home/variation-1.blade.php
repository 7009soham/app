@extends('layouts.app')

@section('title', 'Design Exploration - Variation 1 - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@push('styles')
<link rel="stylesheet" href="{{ \App\Helpers\Asset::versioned('css/design-exploration.css') }}">
<style>
    .preview-v1 .hero-slider {
        min-height: 72vh;
        border-bottom: 1px solid var(--color-gray-200);
    }

    .preview-v1 .slide-overlay {
        background: linear-gradient(100deg, rgba(15, 36, 64, 0.76) 12%, rgba(26, 54, 93, 0.5) 45%, rgba(15, 36, 64, 0.2) 100%);
    }

    .preview-v1 .slide-content {
        max-width: 840px;
        margin: 0 auto;
        text-align: left;
        padding: 0 var(--spacing-6);
    }

    .preview-v1 .slide-content h1 {
        font-size: clamp(2.1rem, 4.8vw, 3.6rem);
        line-height: 1.06;
        margin-bottom: var(--spacing-4);
    }

    .preview-v1 .slide-content p {
        max-width: 640px;
        font-size: var(--font-size-lg);
        margin-bottom: var(--spacing-6);
    }

    .preview-v1 .slider-btn {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.9);
        color: var(--color-primary-dark);
    }

    .preview-v1 .stats-section {
        margin-top: -54px;
        position: relative;
        z-index: 5;
        padding-top: 0;
    }

    .preview-v1 .stat-card {
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
    }

    .preview-v1 .services-section {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border-top: 1px solid var(--color-gray-200);
        border-bottom: 1px solid var(--color-gray-200);
    }

    .preview-v1 .service-card {
        border: 1px solid var(--color-gray-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
    }

    .preview-v1 .service-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-lg);
        border-color: #bfd0ea;
    }

    .preview-v1 .feature-card {
        border-radius: var(--radius-xl);
        border: 1px solid var(--color-gray-200);
        box-shadow: var(--shadow-sm);
    }

    .preview-v1 .cta-section {
        position: relative;
        overflow: hidden;
    }

    .preview-v1 .cta-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.14) 0, transparent 45%), radial-gradient(circle at 88% 72%, rgba(255, 255, 255, 0.18) 0, transparent 38%);
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .preview-v1 .slide-content {
            text-align: center;
            padding: 0 var(--spacing-4);
        }

        .preview-v1 .slide-content p {
            margin-left: auto;
            margin-right: auto;
            font-size: var(--font-size-base);
        }

        .preview-v1 .stats-section {
            margin-top: 0;
            padding-top: var(--spacing-10);
        }
    }
</style>
@endpush

@section('content')
<div class="preview-shell preview-v1">
    @include('design-exploration.home.partials.preview-toolbar', ['active' => 'v1'])

    @include('design-exploration.home.partials.original-home-sections')
</div>
@endsection

@push('scripts')
    @include('design-exploration.home.partials.original-home-scripts')
@endpush
