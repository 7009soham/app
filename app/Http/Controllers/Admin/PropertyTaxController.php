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

        $records = $query->paginate(20);

        // Stats
        $stats = [
            'total_records' => PropertyTaxRecord::count(),
            'total_balance' => PropertyTaxRecord::sum('balance'),
            'total_paid' => PropertyTaxRecord::sum('amount_paid'),
            'pending_count' => PropertyTaxRecord::where('balance', '>', 0)->count(),
        ];

        return view('admin.property-tax.index', compact('records', 'stats'));
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
        return view('admin.property-tax.create', compact('citizens'));
    }

    /**
     * Store a new property tax record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_no' => 'required|string|max:50',
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'a_no' => 'required|integer',
            'monthly_bill' => 'required|numeric|min:0',
            'period' => 'nullable|string|max:100',
            'balance' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'citizen_id' => 'nullable|exists:citizens,id',
        ]);

        $validated['oversize_charge'] = $validated['balance'] > 0 ? $validated['balance'] * 0.10 : 0;

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
        return view('admin.property-tax.edit', compact('propertyTaxRecord', 'citizens'));
    }

    /**
     * Update property tax record
     */
    public function update(Request $request, PropertyTaxRecord $propertyTaxRecord)
    {
        $validated = $request->validate([
            'customer_no' => 'required|string|max:50',
            'customer_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'a_no' => 'required|integer',
            'monthly_bill' => 'required|numeric|min:0',
            'period' => 'nullable|string|max:100',
            'balance' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'citizen_id' => 'nullable|exists:citizens,id',
        ]);

        $validated['oversize_charge'] = $validated['balance'] > 0 ? $validated['balance'] * 0.10 : 0;

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
            fputcsv($file, ['A.No', 'Customer No', 'Customer Name', 'Phone', 'Monthly Bill', 'Period', 'Balance', 'Amount Paid', 'Oversize Charge']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->a_no,
                    $record->customer_no,
                    $record->customer_name,
                    $record->phone,
                    $record->monthly_bill,
                    $record->period,
                    $record->balance,
                    $record->amount_paid,
                    $record->oversize_charge,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
