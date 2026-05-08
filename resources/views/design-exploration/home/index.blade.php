@extends('layouts.app')

@section('title', 'Homepage Design Exploration - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/design-exploration.css') }}">
@endpush

@section('content')
<div class="preview-shell">
    @include('design-exploration.home.partials.preview-toolbar', ['active' => 'index'])

    <section class="preview-page">
        <div class="container">
            <div class="preview-headline">
                <h1>Homepage Design Exploration</h1>
                <p>
                    This sandbox contains three alternate homepage directions for review and comparison.
                    Each version preserves the current portal's visual identity, color system, and content tone,
                    while exploring different layout priorities for clarity and citizen usability.
                </p>
            </div>

            <div class="preview-grid">
                <article class="preview-card">
                    <div class="mock"></div>
                    <div class="card-body">
                        <h3>Variation 1: Action Focused Hero</h3>
                        <p>
                            Prominent hero with instant task cards, compact trust stats, and familiar service tiles.
                            Best for first-time users who need direct entry points.
                        </p>
                        <a href="{{ route('design-exploration.home.variation-1') }}" class="btn btn-primary">Open Variation 1</a>
                    </div>
                </article>

                <article class="preview-card">
                    <div class="mock"></div>
                    <div class="card-body">
                        <h3>Variation 2: Service Command Center</h3>
                        <p>
                            Dashboard-like arrangement with task shortcuts, announcement rail, and operational highlights.
                            Best for frequent citizens who return regularly.
                        </p>
                        <a href="{{ route('design-exploration.home.variation-2') }}" class="btn btn-primary">Open Variation 2</a>
                    </div>
                </article>

                <article class="preview-card">
                    <div class="mock"></div>
                    <div class="card-body">
                        <h3>Variation 3: Trust and Transparency</h3>
                        <p>
                            Story-led structure emphasizing process visibility, timelines, and accountability cues.
                            Best for confidence-building and policy communication.
                        </p>
                        <a href="{{ route('design-exploration.home.variation-3') }}" class="btn btn-primary">Open Variation 3</a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection
