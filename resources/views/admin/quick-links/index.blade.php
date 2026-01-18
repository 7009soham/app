@extends('admin.layouts.app')

@section('title', 'Quick Links')

@section('content')
<div class="page-header d-flex justify-between align-center">
    <div>
        <h1>Quick Links</h1>
        <p>Manage footer and header quick links</p>
    </div>
    <a href="{{ route('admin.quick-links.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Link
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Location</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quickLinks as $link)
                        <tr>
                            <td>
                                @if($link->icon)
                                    <i class="fas {{ $link->icon }}"></i>
                                @endif
                                {{ $link->title }}
                            </td>
                            <td><code>{{ $link->url }}</code></td>
                            <td>
                                <span class="badge {{ $link->location === 'header' ? 'badge-primary' : 'badge-secondary' }}">
                                    {{ ucfirst($link->location) }}
                                </span>
                            </td>
                            <td>{{ $link->order }}</td>
                            <td>
                                <span class="badge {{ $link->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $link->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.quick-links.edit', $link) }}" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.quick-links.destroy', $link) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No quick links found. <a href="{{ route('admin.quick-links.create') }}">Add one now</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge-primary {
        background: #dbeafe;
        color: #1e40af;
    }
</style>
@endpush
