@extends('layouts.app')

@section('title', $page->title . ' - ' . ($settings['site_name'] ?? 'Gram Panchayat'))

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>@if($page->icon)<i class="{{ $page->icon }}"></i> @endif{{ $page->title }}</h1>
        @if($page->summary)
            <p>{{ $page->summary }}</p>
        @endif
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="content-card">
            {{-- Sanitised by RichText on save, so the stored value is safe to render. --}}
            {!! $page->content !!}
        </div>
    </div>
</section>
@endsection
