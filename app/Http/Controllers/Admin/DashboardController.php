<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\TaxPayment;
use App\Models\TaxType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalPayments = TaxPayment::completed()->sum('amount');
        $totalTransactions = TaxPayment::completed()->count();
        $pendingPayments = TaxPayment::pending()->count();
        $failedPayments = TaxPayment::failed()->count();

        // Today's collection
        $todayCollection = TaxPayment::completed()
            ->whereDate('paid_at', today())
            ->sum('amount');
        $todayTransactions = TaxPayment::completed()
            ->whereDate('paid_at', today())
            ->count();

        // This month's collection
        $monthCollection = TaxPayment::completed()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
        $monthTransactions = TaxPayment::completed()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->count();

        // Last month's collection (for comparison)
        $lastMonthCollection = TaxPayment::completed()
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');

        // Calculate growth percentage
        $monthlyGrowth = $lastMonthCollection > 0 
            ? round((($monthCollection - $lastMonthCollection) / $lastMonthCollection) * 100, 1) 
            : 0;

        // Average transaction amount
        $averageTransaction = $totalTransactions > 0 
            ? $totalPayments / $totalTransactions 
            : 0;

        // Payment success rate
        $totalAttempts = TaxPayment::count();
        $successRate = $totalAttempts > 0 
            ? round(($totalTransactions / $totalAttempts) * 100, 1) 
            : 0;

        // Monthly revenue chart data (last 12 months)
        $monthlyRevenue = TaxPayment::completed()
            ->select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->whereDate('paid_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Revenue by tax type
        $revenueByTaxType = TaxPayment::completed()
            ->select('tax_type_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('tax_type_id')
            ->with('taxType')
            ->get();

        // Payment period distribution
        $periodDistribution = TaxPayment::completed()
            ->select('period_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('period_type')
            ->get();

        // Recent transactions
        $recentTransactions = TaxPayment::with('taxType')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Tax types
        $taxTypes = TaxType::withCount(['payments' => function ($query) {
            $query->completed();
        }])->get();

        return view('admin.dashboard', compact(
            'totalPayments',
            'totalTransactions',
            'pendingPayments',
            'failedPayments',
            'todayCollection',
            'todayTransactions',
            'monthCollection',
            'monthTransactions',
            'lastMonthCollection',
            'monthlyGrowth',
            'averageTransaction',
            'successRate',
            'monthlyRevenue',
            'revenueByTaxType',
            'periodDistribution',
            'recentTransactions',
            'taxTypes'
        ));
    }
}
