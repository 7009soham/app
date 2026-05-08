<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use App\Models\Citizen;
use App\Models\TaxAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TaxRateAdjustmentController extends Controller
{
    /**
     * Display the tax rate adjustment page
     */
    public function index()
    {
        // Get summary statistics
        $stats = [
            'total_water_records' => WaterTaxRecord::count(),
            'total_property_records' => PropertyTaxRecord::count(),
            'total_citizens' => Citizen::count(),
            'water_tax_total_monthly' => WaterTaxRecord::sum('monthly_bill'),
            'property_tax_total_monthly' => PropertyTaxRecord::sum('monthly_bill') ?? 0,
        ];

        // Schema uses demand_id; populate the UI from the demands table
        $demands = \App\Models\Demand::orderBy('name')->get();

        // Get adjustment history
        $adjustments = TaxAdjustment::with(['performer', 'reverter'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.tax-rate-adjustment.index', compact('stats', 'demands', 'adjustments'));
    }

    /**
     * Apply tax rate increase
     */
    public function apply(Request $request)
    {
        $request->validate([
            'tax_type' => 'required|in:water,property,both',
            'percentage' => 'required|numeric|min:0.01|max:100',
            'apply_to' => 'required|in:all,selected,customer',
            'selected_citizens' => 'nullable|array',
            'selected_demand_numbers' => 'nullable|array',
            'customer_no' => 'nullable|string|required_if:apply_to,customer',
        ]);

        $percentage = (float) $request->percentage;
        $taxType = $request->tax_type;
        $applyTo = $request->apply_to;
        $selectedDemandNumbers = $request->selected_demand_numbers ?? [];
        $customerNo = $request->customer_no;

        $filters = [];
        if ($applyTo === 'selected') {
            $filters['demand_numbers'] = $selectedDemandNumbers;
        } elseif ($applyTo === 'customer') {
            $filters['customer_no'] = $customerNo;
        }

        $affectedRecords = 0;
        $multiplier = 1 + ($percentage / 100);

        try {
            DB::beginTransaction();

            $updatedRecords = $this->performUpdate($taxType, $multiplier, $applyTo, $filters);
            $affectedRecords = $updatedRecords['water'] + $updatedRecords['property'];

            if ($affectedRecords > 0) {
                TaxAdjustment::create([
                    'tax_type' => $taxType,
                    'percentage' => $percentage,
                    'apply_to' => $applyTo,
                    'filters' => $filters,
                    'affected_records_count' => $affectedRecords,
                    'performed_by' => Auth::guard('admin')->id(),
                ]);

                Log::info("Tax Rate Adjustment: Increased by {$percentage}%", [
                    'affected_records' => $affectedRecords,
                    'multiplier' => $multiplier,
                    'applied_to' => $applyTo,
                    'filters' => $filters
                ]);
            }

            DB::commit();

            return redirect()->route('admin.tax-rate-adjustment.index')
                ->with('success', "Tax rate increased by {$percentage}% successfully! Affected {$affectedRecords} records.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Tax Rate Adjustment failed: " . $e->getMessage());
            return redirect()->route('admin.tax-rate-adjustment.index')
                ->with('error', 'Failed to apply tax rate adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Helper to perform the update logic
     */
    private function performUpdate($taxType, $multiplier, $applyTo, $filters)
    {
        $waterCount = 0;
        $propertyCount = 0;

        // Common filter logic
        $applyFilters = function($query) use ($applyTo, $filters) {
            if ($applyTo === 'selected' && !empty($filters['demand_numbers'])) {
                $citizenIds = Citizen::whereIn('demand_id', $filters['demand_numbers'])->pluck('id');
                $query->whereIn('citizen_id', $citizenIds);
            } elseif ($applyTo === 'customer' && !empty($filters['customer_no'])) {
                 $citizen = Citizen::where('customer_no', $filters['customer_no'])->first();
                 if ($citizen) {
                    $query->where('citizen_id', $citizen->id);
                 } else {
                    // Force empty result if customer not found
                    $query->whereNull('id');
                 }
            }
        };

        // Apply to Water Tax
        if (in_array($taxType, ['water', 'both'])) {
            $query = WaterTaxRecord::query();
            $applyFilters($query);

            $waterRecords = $query->get();
            foreach ($waterRecords as $record) {
                $oldBill = $record->monthly_bill;
                $newBill = round($oldBill * $multiplier, 2);
                $record->monthly_bill = $newBill;
                $record->save();
                $waterCount++;
            }
        }

        // Apply to Property Tax
        if (in_array($taxType, ['property', 'both'])) {
            if (\Schema::hasColumn('property_tax_records', 'monthly_bill')) {
                $query = PropertyTaxRecord::query();
                $applyFilters($query);

                $propertyRecords = $query->get();
                foreach ($propertyRecords as $record) {
                    $oldBill = $record->monthly_bill ?? 0;
                    $newBill = round($oldBill * $multiplier, 2);
                    $record->monthly_bill = $newBill;
                    $record->save();
                    $propertyCount++;
                }
            }
        }

        return ['water' => $waterCount, 'property' => $propertyCount];
    }

    /**
     * Undo a specific adjustment
     */
    public function undo(TaxAdjustment $adjustment)
    {
        if ($adjustment->is_reverted) {
            return redirect()->back()->with('error', 'This adjustment has already been reverted.');
        }

        try {
            DB::beginTransaction();

            $percentage = $adjustment->percentage;
            // Reverse multiplier: New = Current / (1 + P/100)
            $reverseMultiplier = 1 / (1 + ($percentage / 100));

            $revertedCount = $this->performUpdate($adjustment->tax_type, $reverseMultiplier, $adjustment->apply_to, $adjustment->filters ?? []);
            
            $adjustment->is_reverted = true;
            $adjustment->reverted_at = now();
            $adjustment->reverted_by = Auth::guard('admin')->id();
            $adjustment->save();

            DB::commit();
            
            $totalReverted = $revertedCount['water'] + $revertedCount['property'];

            return redirect()->route('admin.tax-rate-adjustment.index')
                ->with('success', "Adjustment reverted successfully. {$totalReverted} records updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to revert adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Get citizens by demand number for AJAX
     */
    public function getCitizensByDemand(Request $request)
    {
        $demandId = $request->input('demand_id') ?? $request->input('demand_number');
        if (empty($demandId)) {
            return response()->json([]);
        }
        
        $citizens = Citizen::where('demand_id', $demandId)
            ->select('id', 'name', 'phone')
            ->withCount('waterTaxRecords')
            ->get();

        return response()->json($citizens);
    }
    
    /**
     * Search citizen by customer number for AJAX
     */
    public function checkCustomer(Request $request)
    {
        $customerNo = $request->customer_no;
        $citizen = Citizen::where('customer_no', $customerNo)->first();
        
        if ($citizen) {
            return response()->json([
                'found' => true,
                'name' => $citizen->name,
                'water_records' => $citizen->waterTaxRecords()->count(),
                'property_records' => $citizen->propertyTaxRecords()->count(),
            ]);
        }
        
        return response()->json(['found' => false]);
    }

    /**
     * Preview the changes before applying
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tax_type' => 'required|in:water,property,both',
            'percentage' => 'required|numeric|min:0.01|max:100',
            'apply_to' => 'required|in:all,selected,customer',
            'selected_demand_numbers' => 'nullable|array',
            'customer_no' => 'nullable|string',
        ]);

        $percentage = (float) $request->percentage;
        $taxType = $request->tax_type;
        $applyTo = $request->apply_to;
        $selectedDemandNumbers = $request->selected_demand_numbers ?? [];
        $customerNo = $request->customer_no;

        $preview = [
            'water_records' => 0,
            'property_records' => 0,
            'current_total' => 0,
            'new_total' => 0,
            'increase_amount' => 0,
        ];

        $multiplier = 1 + ($percentage / 100);
        
        // Common filter logic
        $applyPreviewFilters = function($query) use ($applyTo, $selectedDemandNumbers, $customerNo) {
            if ($applyTo === 'selected' && !empty($selectedDemandNumbers)) {
                $citizenIds = Citizen::whereIn('demand_id', $selectedDemandNumbers)->pluck('id');
                $query->whereIn('citizen_id', $citizenIds);
            } elseif ($applyTo === 'customer' && !empty($customerNo)) {
                 $citizen = Citizen::where('customer_no', $customerNo)->first();
                 if ($citizen) {
                    $query->where('citizen_id', $citizen->id);
                 } else {
                    $query->whereNull('id');
                 }
            }
        };

        // Water Tax preview
        if (in_array($taxType, ['water', 'both'])) {
            $query = WaterTaxRecord::query();
            $applyPreviewFilters($query);

            $preview['water_records'] = $query->count();
            $currentTotal = $query->sum('monthly_bill');
            $preview['current_total'] += $currentTotal;
            $preview['new_total'] += round($currentTotal * $multiplier, 2);
        }

        // Property Tax preview
        if (in_array($taxType, ['property', 'both'])) {
            if (\Schema::hasColumn('property_tax_records', 'monthly_bill')) {
                $query = PropertyTaxRecord::query();
                $applyPreviewFilters($query);

                $preview['property_records'] = $query->count();
                $currentTotal = $query->sum('monthly_bill') ?? 0;
                $preview['current_total'] += $currentTotal;
                $preview['new_total'] += round($currentTotal * $multiplier, 2);
            }
        }

        $preview['increase_amount'] = round($preview['new_total'] - $preview['current_total'], 2);

        return response()->json($preview);
    }
}
