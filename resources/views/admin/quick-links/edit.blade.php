@extends('admin.layouts.app')

@section('title', 'Edit Quick Link')

@section('content')
<div class="page-header">
    <h1>Edit Quick Link</h1>
    <p>{{ $quickLink->title }}</p>
</div>

<div class="card">
    <div class="card-body">
        <form id="quick-link-form" action="{{ route('admin.quick-links.update', $quickLink) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.quick-links._form', ['link' => $quickLink])
        </form>
    </div>
</div>
@endsection
