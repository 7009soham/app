@extends('admin.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">System Activity Logs</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select name="log_name" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($logNames as $name)
                                    <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>
                                        {{ ucfirst($name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                         <div class="col-md-3">
                            <select name="causer_type" class="form-select">
                                <option value="">All Users</option>
                                <option value="admin" {{ request('causer_type') == 'admin' ? 'selected' : '' }}>Admins</option>
                                <option value="citizen" {{ request('causer_type') == 'citizen' ? 'selected' : '' }}>Citizens</option>
                                <option value="system" {{ request('causer_type') == 'system' ? 'selected' : '' }}>System</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search description..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Category</th>
                                <th>User</th>
                                <th>Description</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td style="width: 180px;">
                                    {{ $log->created_at->format('d M Y, h:i A') }}
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->log_name }}</span>
                                </td>
                                <td>
                                    @if($log->causer)
                                        <div class="fw-bold">{{ $log->causer->name }}</div>
                                        <small class="text-muted">
                                            @if(class_basename($log->causer_type) == 'Admin')
                                                <i class="fas fa-user-shield text-primary"></i> Admin
                                            @else
                                                <i class="fas fa-user text-success"></i> Citizen
                                            @endif
                                        </small>
                                    @else
                                        <span class="badge bg-light text-dark">System</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $log->description }}
                                    @if($log->properties && count($log->properties) > 0)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> Details available
                                            </small>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $log->ip_address ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No logs found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
