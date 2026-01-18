<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grievance;
use Illuminate\Http\Request;

class GrievanceController extends Controller
{
    /**
     * Display list of all grievances
     */
    public function index(Request $request)
    {
        $query = Grievance::query()->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search by ticket number or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_no', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $grievances = $query->paginate(15);
        $categories = Grievance::getCategories();

        // Stats
        $stats = [
            'total' => Grievance::count(),
            'pending' => Grievance::pending()->count(),
            'in_progress' => Grievance::inProgress()->count(),
            'resolved' => Grievance::resolved()->count(),
        ];

        return view('admin.grievances.index', compact('grievances', 'categories', 'stats'));
    }

    /**
     * Show grievance details
     */
    public function show(Grievance $grievance)
    {
        return view('admin.grievances.show', compact('grievance'));
    }

    /**
     * Update grievance status
     */
    public function updateStatus(Request $request, Grievance $grievance)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'admin_remarks' => 'nullable|string|max:1000',
        ]);

        $grievance->update($validated);

        if ($validated['status'] === 'resolved') {
            $grievance->update(['resolved_at' => now()]);
        }

        return back()->with('success', 'Grievance status updated successfully.');
    }

    /**
     * Delete grievance
     */
    public function destroy(Grievance $grievance)
    {
        // Delete image if exists
        if ($grievance->image) {
            \Storage::disk('public')->delete($grievance->image);
        }

        $grievance->delete();

        return redirect()->route('admin.grievances.index')
            ->with('success', 'Grievance deleted successfully.');
    }
}
