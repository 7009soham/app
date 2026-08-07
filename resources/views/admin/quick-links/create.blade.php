@extends('admin.layouts.app')

@section('title', 'Add Quick Link')

@section('content')
<div class="page-header">
    <h1>Add Quick Link</h1>
    <p>Link to an existing address, or write the page here</p>
</div>

<div class="card">
    <div class="card-body">
        <form id="quick-link-form" action="{{ route('admin.quick-links.store') }}" method="POST">
            @csrf
            @include('admin.quick-links._form', ['link' => null])
        </form>
    </div>
</div>
@endsection
