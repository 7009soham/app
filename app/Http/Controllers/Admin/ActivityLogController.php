<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Restrict to Super Admin
        $currentAdmin = Auth::guard('admin')->user();
        if (!$currentAdmin || !$currentAdmin->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Unauthorized access.');
        }

        $query = ActivityLog::with('causer')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
                // Adding simple relationship check might be complex with morph, keeping it simple for now
            });
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        
        if ($request->filled('causer_type')) {
            if ($request->causer_type == 'admin') {
                $query->where('causer_type', 'App\Models\Admin');
            } elseif ($request->causer_type == 'citizen') {
                $query->where('causer_type', 'App\Models\Citizen');
            } else {
                 $query->whereNull('causer_type'); // System
            }
        }

        $logs = $query->paginate(20);
        
        $logNames = ActivityLog::distinct()->pluck('log_name');

        return view('admin.activity-logs.index', compact('logs', 'logNames'));
    }
}
