<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Citizen;
use App\Models\Payment;
use App\Models\WaterTaxRecord;
use App\Models\PropertyTaxRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // ---------------------------------------------------------
        // 1. Monthly Performance Analytics (Last 12 Months)
        // ---------------------------------------------------------
        $monthlyAnalytics = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $monthName = $date->format('M Y');

            // Total Billed in this month (Generated bills)
            $totalBilled = \App\Models\MonthlyTaxBill::where('bill_year', $year)
                ->where('bill_month', $month)
                ->sum('bill_amount');

            // Total Collected in this month (Actual payments made)
            $totalCollected = Payment::whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->where('status', 'completed')
                ->sum('amount');

            $monthlyAnalytics[] = [
                'month' => $monthName,
                'billed' => (float)$totalBilled,
                'collected' => (float)$totalCollected,
            ];
        }

        // ---------------------------------------------------------
        // 2. Demand-wise Analytics (Existing Logic)
        // ---------------------------------------------------------
        $demandData = [];
        foreach (Demand::orderBy('id')->get() as $demand) {
            $demandData[$demand->id] = [
                'demand_id' => $demand->id,
                'demand_name' => $demand->name,
                'total_citizens' => 0,
                'water_tax_paid' => 0,
                'property_tax_paid' => 0,
                'total_paid' => 0,
                'water_balance' => 0,
                'property_balance' => 0,
                'total_balance' => 0,
            ];
        }

        // Citizen Counts by Demand Number
        $citizenCounts = Citizen::select('demand_id', DB::raw('count(*) as count'))
            ->whereNotNull('demand_id')
            ->groupBy('demand_id')
            ->pluck('count', 'demand_id');

        foreach ($citizenCounts as $demand => $count) {
               if (isset($demandData[$demand])) {
                $demandData[$demand]['total_citizens'] = $count;
             }
        }

        // Payments Grouped by Citizen's Demand Number
        $payments = Payment::select('citizens.demand_id', 'payments.tax_type', DB::raw('sum(payments.amount) as total'))
            ->join('citizens', 'payments.citizen_id', '=', 'citizens.id')
            ->whereNotNull('citizens.demand_id')
            ->where('payments.status', 'completed')
            ->groupBy('citizens.demand_id', 'payments.tax_type')
            ->get();

        foreach ($payments as $payment) {
            $demand = $payment->demand_id;
            if (isset($demandData[$demand])) {
                if ($payment->tax_type == 'water_tax') {
                    $demandData[$demand]['water_tax_paid'] += $payment->total;
                } else {
                    $demandData[$demand]['property_tax_paid'] += $payment->total;
                }
                $demandData[$demand]['total_paid'] += $payment->total;
            }
        }
        
        // Water Tax Pending Balance
        $waterPending = WaterTaxRecord::select('citizens.demand_id', DB::raw('sum(water_tax_records.balance) as pending'))
             ->join('citizens', 'water_tax_records.citizen_id', '=', 'citizens.id')
             ->whereNotNull('citizens.demand_id')
             ->groupBy('citizens.demand_id')
             ->pluck('pending', 'demand_id');
             
        foreach ($waterPending as $demand => $pending) {
            if (isset($demandData[$demand])) {
                $demandData[$demand]['water_balance'] = $pending;
                $demandData[$demand]['total_balance'] += $pending;
            }
        }

        // Property Tax Pending Balance
        $propertyPending = PropertyTaxRecord::select('citizens.demand_id', DB::raw('sum(property_tax_records.balance) as pending'))
             ->join('citizens', 'property_tax_records.citizen_id', '=', 'citizens.id')
             ->whereNotNull('citizens.demand_id')
             ->groupBy('citizens.demand_id')
             ->pluck('pending', 'demand_id');

        foreach ($propertyPending as $demand => $pending) {
            if (isset($demandData[$demand])) {
                $demandData[$demand]['property_balance'] = $pending;
                $demandData[$demand]['total_balance'] += $pending;
            }
        }

        return view('admin.analytics.index', compact('demandData', 'monthlyAnalytics'));
    }
}
