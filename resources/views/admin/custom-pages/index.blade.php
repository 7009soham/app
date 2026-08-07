@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
<div class="page-header d-flex justify-between align-center">
    <div>
        <h1>Pages</h1>
        <p>Create and edit public pages without a developer</p>
    </div>
    <a href="{{ route('admin.custom-pages.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Page
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Public URL</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td>
                                @if($page->icon)<i class="{{ $page->icon }}"></i> @endif
                                <strong>{{ $page->title }}</strong>
                                @if($page->excerpt)
                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                        {{ \Illuminate\Support\Str::limit($page->excerpt, 70) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <code>/page/{{ $page->slug }}</code>
                                @if($page->is_published)
                                    <a href="{{ $page->url }}" target="_blank" rel="noopener"
                                       title="Open page" style="margin-left: 6px;">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </td>
                            <td>{{ $page->order }}</td>
                            <td>
                                <span class="badge {{ $page->is_published ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $page->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.custom-pages.edit', $page) }}" class="btn btn-sm btn-outline">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.custom-pages.destroy', $page) }}" method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Delete “{{ $page->title }}”? Any quick link pointing at /page/{{ $page->slug }} will break.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline" style="color: #dc2626;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 28px; color: #64748b;">
                                No pages yet.
                                <a href="{{ route('admin.custom-pages.create') }}">Create one now</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pages->isNotEmpty())
            <p style="margin: 14px 0 0; font-size: 13px; color: #64748b;">
                <i class="fas fa-info-circle"></i>
                To show a page in the footer, add a
                <a href="{{ route('admin.quick-links.index') }}">quick link</a>
                pointing at its <code>/page/…</code> URL.
            </p>
        @endif
    </div>
</div>
@endsection
