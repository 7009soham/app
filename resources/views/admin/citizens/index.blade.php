@extends('admin.layouts.app')

@section('title', 'Citizen Management')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }

    .data-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }

    .btn-view {
        background: #eff6ff;
        color: #2563eb;
    }

    .btn-view:hover {
        background: #dbeafe;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-users" style="color: #6366f1;"></i> Citizen Management</h1>
    <div style="display: flex; gap: 12px; align-items: center;">
        <select id="bulkActionSelect" style="padding: 10px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <option value="">Bulk Actions</option>
            <option value="delete">Delete Selected</option>
            <option value="export">Export Selected</option>
        </select>
        <button type="button" id="applyBulkActionBtn" style="padding: 10px 16px; background: #1e3a5f; color: white; border: none; border-radius: 8px; cursor: pointer;">Apply</button>
        
        @if(auth()->guard('admin')->user()->hasPermission('citizens.create'))
        <a href="{{ route('admin.citizens.create') }}" style="padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Add Citizen
        </a>
        @endif
    </div>
</div>

<!-- Filters -->
<div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <form method="GET" action="{{ route('admin.citizens.index') }}">
        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="search" placeholder="Search by Name, Phone, or Customer No..." value="{{ request('search') }}" 
                       style="width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px;">
            </div>
            <button type="submit" style="padding: 10px 20px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-weight: 500; cursor: pointer;">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.citizens.index') }}" style="padding: 10px 20px; background: #f1f5f9; color: #64748b; text-decoration: none; border-radius: 8px; font-weight: 500;">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card">
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                    <th>Customer No</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Detailed Address</th>
                    <th>Registered On</th>
                    <th>Status</th>
                    <th>Secret Login</th>
                </tr>
            </thead>
            <tbody>
                @forelse($citizens as $citizen)
                <tr>
                    <td><input type="checkbox" class="row-checkbox" value="{{ $citizen->id }}"></td>
                    <td style="font-family: monospace;">{{ $citizen->customer_no }}</td>
                    <td style="font-weight: 600; color: #1e293b;">{{ $citizen->name }}</td>
                    <td>
                        <div>{{ $citizen->phone }}</div>
                        <div style="font-size: 12px; color: #64748b;">{{ $citizen->email ?: '-' }}</div>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($citizen->address, 30) }}</td>
                    <td>{{ $citizen->created_at->format('d M Y') }}</td>
                    <td>
                        @if($citizen->phone_verified_at)
                        <span style="background:#dcfce7; color:#16a34a; padding:4px 10px; border-radius:20px; font-size:12px; font-weight: 600;">Verified</span>
                        @else
                        <span style="background:#fee2e2; color:#dc2626; padding:4px 10px; border-radius:20px; font-size:12px; font-weight: 600;">Unverified</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            @if(auth()->guard('admin')->user()->hasPermission('citizens.impersonate'))
                            <form action="{{ route('admin.login-as-citizen') }}" method="POST" target="_blank" style="display:inline;">
                                @csrf
                                <input type="hidden" name="phone" value="{{ $citizen->phone }}">
                                <button type="submit" class="btn-action btn-view" title="Login as Citizen">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </form>
                            @endif
                            
                             @if(auth()->guard('admin')->user()->hasPermission('citizens.edit'))
                            <a href="{{ route('admin.citizens.edit', $citizen) }}" class="btn-action" style="background:#fef3c7; color:#d97706;" title="Edit Profile">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @endif

                            @if(auth()->guard('admin')->user()->hasPermission('water_tax.view'))
                            <a href="{{ route('admin.water-tax.index', ['search' => $citizen->customer_no]) }}" class="btn-action" style="background:#dbeafe; color:#2563eb;" title="Manage Water Tax">
                                <i class="fas fa-tint"></i> Water
                            </a>
                            @endif

                            @if(auth()->guard('admin')->user()->hasPermission('property_tax.view'))
                            <a href="{{ route('admin.property-tax.index', ['search' => $citizen->customer_no]) }}" class="btn-action" style="background:#dcfce7; color:#16a34a;" title="Manage Property Tax">
                                <i class="fas fa-home"></i> Property
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                        <i class="fas fa-users" style="font-size: 40px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                        No citizens found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($citizens->hasPages())
    <div style="padding: 20px; border-top: 1px solid #f1f5f9;">
        {{ $citizens->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
    
    document.getElementById('applyBulkActionBtn')?.addEventListener('click', function() {
        let action = document.getElementById('bulkActionSelect').value;
        if (!action) {
            alert('Please select a bulk action');
            return;
        }
        
        let selected = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => selected.push(cb.value));
        
        if (selected.length === 0) {
            alert('Please select at least one record');
            return;
        }
        
        if (confirm('Are you sure you want to ' + action + ' ' + selected.length + ' records?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.citizens.bulk") }}';
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            let actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            selected.forEach(id => {
                let idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'ids[]';
                idInput.value = id;
                form.appendChild(idInput);
            });

            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endpush
