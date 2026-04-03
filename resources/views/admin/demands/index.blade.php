@extends('admin.layouts.app')

@section('title', 'Manage Demands')

@section('content')
<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <h1 class="page-title" style="font-size: 24px; font-weight: 700; color: #1e293b;">
        Manage Demands
    </h1>
    <a href="{{ route('admin.demands.create') }}" 
       style="padding: 10px 20px; background: #6366f1; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">
        <i class="fas fa-plus"></i> Add Demand
    </a>
</div>

<div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 24px;">
    @if(session('success'))
        <div style="padding: 12px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">ID</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">Name</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">Description</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">Citizens</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">Water Tax</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">Property Tax</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600;">Assessments</th>
                <th style="padding: 12px; text-align: right; color: #64748b; font-weight: 600;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demands as $demand)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 12px;">{{ $demand->id }}</td>
                <td style="padding: 12px; font-weight: 500;">{{ $demand->name }}</td>
                <td style="padding: 12px; color: #64748b;">{{ Str::limit($demand->description, 50) }}</td>
                <td style="padding: 12px;">{{ $demand->citizens_count }}</td>
                <td style="padding: 12px;">{{ $demand->water_tax_records_count }}</td>
                <td style="padding: 12px;">{{ $demand->property_tax_records_count }}</td>
                <td style="padding: 12px;">{{ $demand->property_assessments_count }}</td>
                <td style="padding: 12px; text-align: right;">
                    <a href="{{ route('admin.demands.edit', $demand->id) }}" style="color: #3b82f6; margin-right: 12px;"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('admin.demands.destroy', $demand->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this demand?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        {{ $demands->links() }}
    </div>
</div>
@endsection
