@extends('admin.layouts.app')

@section('title', 'Sliders')

@section('content')
<div class="page-header d-flex justify-between align-center">
    <div>
        <h1>Sliders</h1>
        <p>Manage homepage slider images</p>
    </div>
    <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Slider
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Subtitle</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sliders as $slider)
                        <tr>
                            <td>
                                <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>{{ $slider->title ?? '-' }}</td>
                            <td>{{ $slider->subtitle ?? '-' }}</td>
                            <td>{{ $slider->order }}</td>
                            <td>
                                <span class="badge {{ $slider->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $slider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this slider?');">
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
                            <td colspan="6" class="text-center text-muted">No sliders found. <a href="{{ route('admin.sliders.create') }}">Add one now</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
