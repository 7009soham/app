@extends('admin.layouts.app')

@section('title', 'Edit Page')

@section('content')
<div class="page-header d-flex justify-between align-center">
    <div>
        <h1>Edit Page</h1>
        <p>{{ $page->title }}</p>
    </div>
    @if($page->is_published)
        <a href="{{ $page->url }}" target="_blank" rel="noopener" class="btn btn-outline">
            <i class="fas fa-external-link-alt"></i> View Page
        </a>
    @endif
</div>

<form id="custom-page-form" action="{{ route('admin.custom-pages.update', $page) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.custom-pages._form', ['page' => $page])
</form>
@endsection
