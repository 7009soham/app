<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Citizen;
use Illuminate\Http\Request;

class CitizenController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->guard('admin')->user()->hasPermission('citizens.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Citizen::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $citizens = $query->latest()->paginate(20);

        return view('admin.citizens.index', compact('citizens'));
    }
    public function create()
    {
        if (!auth()->guard('admin')->user()->hasPermission('citizens.create')) {
            abort(403, 'Unauthorized action.');
        }

        $demands = \App\Models\Demand::all();
        return view('admin.citizens.create', compact('demands'));
    }

    public function store(Request $request)
    {
        if (!auth()->guard('admin')->user()->hasPermission('citizens.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15|unique:citizens,phone',
            'email' => 'nullable|email|max:255|unique:citizens,email',
            'address' => 'nullable|string',
            'customer_no' => 'required|string|unique:citizens,customer_no',
            'aadhar_card' => 'nullable|string|size:12',
            'demand_id' => 'nullable|exists:demands,id',
        ]);
        
        Citizen::create($validated);
        
        return redirect()->route('admin.citizens.index')
            ->with('success', 'Citizen created successfully.');
    }

    public function edit(Citizen $citizen)
    {
        if (!auth()->guard('admin')->user()->hasPermission('citizens.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $demands = \App\Models\Demand::all();
        return view('admin.citizens.edit', compact('citizen', 'demands'));
    }

    public function update(Request $request, Citizen $citizen)
    {
        if (!auth()->guard('admin')->user()->hasPermission('citizens.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15|unique:citizens,phone,' . $citizen->id,
            'email' => 'nullable|email|max:255|unique:citizens,email,' . $citizen->id,
            'address' => 'nullable|string',
            'customer_no' => 'required|string|unique:citizens,customer_no,' . $citizen->id,
            'aadhar_card' => 'nullable|string|size:12',
            'demand_id' => 'nullable|exists:demands,id',
        ]);
        
        $citizen->update($validated);
        
        return redirect()->route('admin.citizens.index')
            ->with('success', 'Citizen profile updated successfully.');
    }

    public function bulk(Request $request)
    {
        if (!auth()->guard('admin')->user()->hasPermission('citizens.edit') && !auth()->guard('admin')->user()->hasPermission('citizens.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No records selected.');
        }

        if ($action === 'delete') {
            Citizen::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' citizens deleted successfully.');
        }

        if ($action === 'export') {
            $csvData = "ID,Name,Phone,Email,Customer No\n";
            $citizens = Citizen::whereIn('id', $ids)->get();
            foreach ($citizens as $citizen) {
                $csvData .= "{$citizen->id},{$citizen->name},{$citizen->phone},{$citizen->email},{$citizen->customer_no}\n";
            }
            return response($csvData)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="citizens_export.csv"');
        }

        return back()->with('error', 'Invalid action selected.');
    }
}
