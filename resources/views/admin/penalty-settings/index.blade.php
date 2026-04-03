@extends('admin.layouts.app')

@section('title', 'Penalty Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Penalty Settings</h4>
            <a href="{{ route('admin.penalty-settings.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>How it works:</strong> Penalty is applied to unpaid bills after the grace period has passed. 
                    Only <strong>one active setting</strong> per tax type is allowed.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Tax Type</th>
                                <th>Grace Period</th>
                                <th>Penalty %</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                            <tr>
                                <td>
                                    <strong>{{ $setting->name }}</strong>
                                    @if($setting->description)
                                        <br><small class="text-muted">{{ $setting->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($setting->tax_type == 'water_tax')
                                        <span class="badge bg-info">Water Tax</span>
                                    @else
                                        <span class="badge bg-success">Property Tax</span>
                                    @endif
                                </td>
                                <td>{{ $setting->grace_days }} days</td>
                                <td class="text-danger fw-bold">{{ $setting->penalty_percentage }}%</td>
                                <td>
                                    @if($setting->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.penalty-settings.edit', $setting) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.penalty-settings.destroy', $setting) }}" method="POST" 
                                              onsubmit="return confirm('Delete this penalty setting?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No penalty settings configured yet.</p>
                                    <a href="{{ route('admin.penalty-settings.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Create First Setting
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
