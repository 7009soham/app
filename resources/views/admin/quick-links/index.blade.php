{{--
    Pilot page for the Tailwind + DaisyUI admin. Extends
    admin.layouts.tailwind, not admin.layouts.app, so it is the only page in
    the panel using the new stylesheet.
--}}
@extends('admin.layouts.tailwind')

@section('title', 'Quick Links')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-base-content">Quick Links</h1>
        <p class="text-sm text-base-content/60 mt-1">Manage footer and header quick links</p>
    </div>
    <a href="{{ route('admin.quick-links.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Link
    </a>
</div>

<div class="card bg-base-100 border border-base-300 shadow-sm">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr class="bg-base-200 text-base-content/70">
                        <th>Title</th>
                        <th>URL</th>
                        <th>Location</th>
                        <th class="text-right">Order</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quickLinks as $link)
                        <tr class="hover:bg-base-200/60">
                            <td>
                                <div class="flex items-center gap-2 font-medium">
                                    @if($link->icon)
                                        <i class="fas {{ $link->icon }} text-base-content/40 w-4 text-center"></i>
                                    @endif
                                    {{ $link->title }}
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $link->url }}</code>
                                    @if($link->ownsItsPage())
                                        <span class="badge badge-info badge-sm">page</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft {{ $link->location === 'header' ? 'badge-primary' : 'badge-neutral' }}">
                                    {{ ucfirst($link->location) }}
                                </span>
                            </td>
                            <td class="text-right tabular-nums text-base-content/70">{{ $link->order }}</td>
                            <td>
                                @if($link->is_active)
                                    <span class="badge badge-success badge-soft gap-1">
                                        <i class="fas fa-circle text-[6px]"></i> Active
                                    </span>
                                @else
                                    <span class="badge badge-ghost">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.quick-links.edit', $link) }}"
                                       class="btn btn-ghost btn-sm btn-square" aria-label="Edit {{ $link->title }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.quick-links.destroy', $link) }}" method="POST"
                                          onsubmit="return confirm('Delete “{{ $link->title }}”?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-ghost btn-sm btn-square text-error hover:bg-error hover:text-error-content"
                                                aria-label="Delete {{ $link->title }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="text-center py-12">
                                    <i class="fas fa-link text-3xl text-base-content/20"></i>
                                    <p class="mt-3 font-medium">No quick links yet</p>
                                    <p class="text-sm text-base-content/60 mt-1">
                                        <a href="{{ route('admin.quick-links.create') }}" class="link link-primary">Add your first link</a>
                                        to fill the footer.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($quickLinks->isNotEmpty())
    <div role="alert" class="alert alert-info alert-soft mt-5">
        <i class="fas fa-circle-info"></i>
        <span>
            Links marked <span class="badge badge-info badge-sm">page</span> have their content written
            in the link form. Others point at an existing address, so check those still resolve.
        </span>
    </div>
@endif
@endsection
