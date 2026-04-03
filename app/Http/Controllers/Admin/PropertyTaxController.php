<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyTaxRecord;
use App\Models\Citizen;
use Illuminate\Http\Request;

class PropertyTaxController extends Controller
{
    /**
     * Display Property Tax dashboard
     */
    public function index(Request $request)
    {
        $query = PropertyTaxRecord::with('citizen')->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_no', 'like', "%{$search}%")
                  ->orWhere('property_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by balance status
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('balance', '>', 0);
            } else {
                $query->where('balance', '<=', 0);
            }
        }

        // Filter by Demand
        if ($request->filled('demand_id')) {
            $query->where('demand_id', $request->demand_id);
        }

        $records = $query->paginate(20);

        $demands = \App\Models\Demand::all();

        // Stats - Calculate total paid as current_total minus balance
        $totalCurrent = PropertyTaxRecord::sum('current_total');
        $totalBalance = PropertyTaxRecord::sum('balance');
        $totalPaid = $totalCurrent - $totalBalance;

        $stats = [
            'total_records' => PropertyTaxRecord::count(),
            'total_balance' => $totalBalance,
            'total_paid' => $totalPaid,
            'pending_count' => PropertyTaxRecord::where('balance', '>', 0)->count(),
        ];

        return view('admin.property-tax.index', compact('records', 'stats', 'demands'));
    }

    /**
     * Show property tax record details
     */
    public function show(PropertyTaxRecord $propertyTaxRecord)
    {
        $propertyTaxRecord->load('citizen');
        return view('admin.property-tax.show', compact('propertyTaxRecord'));
    }

    /**
     * Show form to create a new record
     */
    public function create()
    {
        $citizens = Citizen::orderBy('name')->get();
        $demands = \App\Models\Demand::all();
        return view('admin.property-tax.create', compact('citizens', 'demands'));
    }

    /**
     * Store a new property tax record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'a_no' => 'required|integer',
            'customer_no' => 'required|string|max:50',
            'property_no' => 'required|string|max:50',
            'property_type' => 'required|string|max:50',
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'aadhaar_no' => 'nullable|string|max:20',
            'previous_house_tax' => 'nullable|numeric|min:0',
            'previous_electricity_tax' => 'nullable|numeric|min:0',
            'previous_health_tax' => 'nullable|numeric|min:0',
            'previous_total' => 'nullable|numeric|min:0',
            'current_house_tax' => 'required|numeric|min:0',
            'current_electricity_tax' => 'required|numeric|min:0',
            'current_health_tax' => 'required|numeric|min:0',
            'current_total' => 'required|numeric|min:0',
            'balance' => 'required|numeric|min:0',
            'citizen_id' => 'nullable|exists:citizens,id',
            'demand_id' => 'nullable|exists:demands,id',
        ]);

        PropertyTaxRecord::create($validated);

        return redirect()->route('admin.property-tax.index')
            ->with('success', 'Property tax record created successfully.');
    }

    /**
     * Show form to edit a record
     */
    public function edit(PropertyTaxRecord $propertyTaxRecord)
    {
        $citizens = Citizen::orderBy('name')->get();
        $demands = \App\Models\Demand::all();
        return view('admin.property-tax.edit', compact('propertyTaxRecord', 'citizens', 'demands'));
    }

    /**
     * Update property tax record
     */
    public function update(Request $request, PropertyTaxRecord $propertyTaxRecord)
    {
        $validated = $request->validate([
            'a_no' => 'required|integer',
            'customer_no' => 'required|string|max:50',
            'property_no' => 'required|string|max:50',
            'property_type' => 'required|string|max:50',
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'aadhaar_no' => 'nullable|string|max:20',
            'previous_house_tax' => 'nullable|numeric|min:0',
            'previous_electricity_tax' => 'nullable|numeric|min:0',
            'previous_health_tax' => 'nullable|numeric|min:0',
            'previous_total' => 'nullable|numeric|min:0',
            'current_house_tax' => 'required|numeric|min:0',
            'current_electricity_tax' => 'required|numeric|min:0',
            'current_health_tax' => 'required|numeric|min:0',
            'current_total' => 'required|numeric|min:0',
            'balance' => 'required|numeric|min:0',
            'citizen_id' => 'nullable|exists:citizens,id',
            'demand_id' => 'nullable|exists:demands,id',
        ]);

        $propertyTaxRecord->update($validated);

        return redirect()->route('admin.property-tax.index')
            ->with('success', 'Property tax record updated successfully.');
    }

    /**
     * Delete property tax record
     */
    public function destroy(PropertyTaxRecord $propertyTaxRecord)
    {
        $propertyTaxRecord->delete();

        return redirect()->route('admin.property-tax.index')
            ->with('success', 'Property tax record deleted successfully.');
    }

    /**
     * Export property tax records
     */
    public function export(Request $request)
    {
        $records = PropertyTaxRecord::orderBy('a_no')->get();

        $filename = 'property_tax_records_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'A.No', 
                'Property No', 
                'Property Type',
                'Customer Name', 
                'Phone', 
                'Aadhaar No',
                'Previous House Tax',
                'Previous Electricity Tax',
                'Previous Health Tax',
                'Previous Total',
                'Current House Tax',
                'Current Electricity Tax',
                'Current Health Tax',
                'Current Total',
                'Balance'
            ]);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->a_no,
                    $record->property_no,
                    $record->property_type,
                    $record->customer_name,
                    $record->phone,
                    $record->aadhaar_no,
                    $record->previous_house_tax,
                    $record->previous_electricity_tax,
                    $record->previous_health_tax,
                    $record->previous_total,
                    $record->current_house_tax,
                    $record->current_electricity_tax,
                    $record->current_health_tax,
                    $record->current_total,
                    $record->balance,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk actions for property tax records
     */
    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No records selected.');
        }

        if ($action === 'delete') {
            PropertyTaxRecord::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' property tax records deleted successfully.');
        }

        if ($action === 'mark_paid') {
            foreach (PropertyTaxRecord::whereIn('id', $ids)->get() as $record) {
                // simple logic to mark as paid
                $record->balance = 0;
                $record->save();
            }
            return back()->with('success', count($ids) . ' property tax records marked as paid.');
        }

        if ($action === 'export') {
            $records = PropertyTaxRecord::whereIn('id', $ids)->orderBy('a_no')->get();
            $filename = 'property_tax_records_bulk_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function () use ($records) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'A.No', 'Property No', 'Property Type', 'Customer Name', 'Phone', 'Aadhaar No',
                    'Previous House Tax', 'Previous Electricity Tax', 'Previous Health Tax', 'Previous Total',
                    'Current House Tax', 'Current Electricity Tax', 'Current Health Tax', 'Current Total', 'Balance'
                ]);
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->a_no, $record->property_no, $record->property_type, $record->customer_name,
                        $record->phone, $record->aadhaar_no, $record->previous_house_tax, $record->previous_electricity_tax,
                        $record->previous_health_tax, $record->previous_total, $record->current_house_tax,
                        $record->current_electricity_tax, $record->current_health_tax, $record->current_total, $record->balance,
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Invalid action selected.');
    }
}
