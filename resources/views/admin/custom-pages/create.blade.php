@extends('admin.layouts.app')

@section('title', 'Add Page')

@section('content')
<div class="page-header">
    <h1>Add Page</h1>
    <p>Publish a new page to the public site</p>
</div>

<form id="custom-page-form" action="{{ route('admin.custom-pages.store') }}" method="POST">
    @csrf
    @include('admin.custom-pages._form', ['page' => null])
</form>
@endsection
