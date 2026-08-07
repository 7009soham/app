<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Csv;
use App\Http\Controllers\Controller;
use App\Models\WaterTaxRecord;
use App\Models\Citizen;
use Illuminate\Http\Request;

class WaterTaxController extends Controller
{
    /**
     * Display Water Tax dashboard
     */
    public function index(Request $request)
    {
        $query = WaterTaxRecord::with('citizen')->orderBy('created_at', 'desc');

        // Search
        $query->search($request->input('search'));

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

        $records = $query->paginate(20)->withQueryString();

        $demands = \App\Models\Demand::all();

        // Stats
        $stats = [
            'total_records' => WaterTaxRecord::count(),
            'total_balance' => WaterTaxRecord::sum('balance'),
            'total_paid' => WaterTaxRecord::sum('amount_paid'),
            'pending_count' => WaterTaxRecord::where('balance', '>', 0)->count(),
        ];

        return view('admin.water-tax.index', compact('records', 'stats', 'demands'));
    }

    /**
     * Show water tax record details
     */
    public function show(WaterTaxRecord $waterTaxRecord)
    {
        $waterTaxRecord->load(['citizen.demand']);
        $demand = $waterTaxRecord->demand_id ? \App\Models\Demand::find($waterTaxRecord->demand_id) : null;
        return view('admin.water-tax.show', compact('waterTaxRecord', 'demand'));
    }

    /**
     * Show form to create a new record
     */
    public function create()
    {
        $citizens = Citizen::orderBy('name')->get();
        $demands = \App\Models\Demand::all();
        return view('admin.water-tax.create', compact('citizens', 'demands'));
    }

    /**
     * Store a new water tax record
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
            'demand_id' => 'nullable|exists:demands,id',
        ]);

        // DB column has a default of 0 and is NOT NULL; normalize blank input.
        $validated['amount_paid'] = $validated['amount_paid'] ?? 0;

        $validated['oversize_charge'] = $validated['balance'] > 0 ? $validated['balance'] * 0.10 : 0;

        WaterTaxRecord::create($validated);

        return redirect()->route('admin.water-tax.index')
            ->with('success', 'Water tax record created successfully.');
    }

    /**
     * Show form to edit a record
     */
    public function edit(WaterTaxRecord $waterTaxRecord)
    {
        $citizens = Citizen::orderBy('name')->get();
        $demands = \App\Models\Demand::all();
        return view('admin.water-tax.edit', compact('waterTaxRecord', 'citizens', 'demands'));
    }

    /**
     * Update water tax record
     */
    public function update(Request $request, WaterTaxRecord $waterTaxRecord)
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
            'demand_id' => 'nullable|exists:demands,id',
        ]);

        // DB column has a default of 0 and is NOT NULL; normalize blank input.
        $validated['amount_paid'] = $validated['amount_paid'] ?? 0;

        $validated['oversize_charge'] = $validated['balance'] > 0 ? $validated['balance'] * 0.10 : 0;

        $waterTaxRecord->update($validated);

        return redirect()->route('admin.water-tax.index')
            ->with('success', 'Water tax record updated successfully.');
    }

    /**
     * Delete water tax record
     */
    public function destroy(WaterTaxRecord $waterTaxRecord)
    {
        $waterTaxRecord->delete();

        return redirect()->route('admin.water-tax.index')
            ->with('success', 'Water tax record deleted successfully.');
    }

    /**
     * Export water tax records
     */
    public function export(Request $request)
    {
        $records = WaterTaxRecord::orderBy('a_no')->get();

        $filename = 'water_tax_records_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            Csv::writeBom($file);
            fputcsv($file, ['A.No', 'Customer No', 'Customer Name', 'Phone', 'Monthly Bill', 'Period', 'Balance', 'Amount Paid', 'Oversize Charge']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->a_no,
                    Csv::text($record->customer_no),
                    $record->customer_name,
                    Csv::text($record->phone),
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

    /**
     * Bulk actions for water tax records
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,mark_paid,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|distinct|exists:water_tax_records,id',
        ]);

        $action = $validated['action'];
        $ids = $validated['ids'];

        if ($action === 'delete') {
            $deleted = WaterTaxRecord::whereIn('id', $ids)->delete();
            return back()->with('success', $deleted . ' water tax records deleted successfully.');
        }

        if ($action === 'mark_paid') {
            $processed = 0;
            $skipped = 0;

            foreach (WaterTaxRecord::whereIn('id', $ids)->get() as $record) {
                $balance = (float) ($record->balance ?? 0);

                if ($balance <= 0) {
                    $skipped++;
                    continue;
                }

                $record->amount_paid = (float) ($record->amount_paid ?? 0) + $balance;
                $record->balance = 0;
                $record->oversize_charge = 0;
                $record->save();

                $processed++;
            }

            $message = $processed . ' water tax records marked as paid.';
            if ($skipped > 0) {
                $message .= ' ' . $skipped . ' already-settled records skipped.';
            }

            return back()->with('success', $message);
        }

        if ($action === 'export') {
            $records = WaterTaxRecord::whereIn('id', $ids)->orderBy('a_no')->get();

            $filename = 'water_tax_records_bulk_' . date('Y-m-d') . '.csv';
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

        return back()->with('error', 'Invalid action selected.');
    }
}
