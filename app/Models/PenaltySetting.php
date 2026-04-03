<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenaltySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type',
        'name',
        'grace_days',
        'penalty_percentage',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'penalty_percentage' => 'decimal:2',
    ];

    /**
     * Get active penalty setting for a tax type
     */
    public static function getActiveSetting($taxType)
    {
        return static::where('tax_type', $taxType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Calculate penalty for a given bill
     * 
     * @param MonthlyTaxBill $bill
     * @return array ['penalty_amount' => float, 'days_overdue' => int, 'penalty_percentage' => float]
     */
    public static function calculatePenalty(MonthlyTaxBill $bill)
    {
        if ($bill->balance <= 0 || $bill->status === 'paid') {
            return ['penalty_amount' => 0, 'days_overdue' => 0, 'penalty_percentage' => 0];
        }

        $setting = static::getActiveSetting($bill->tax_type);
        
        if (!$setting) {
            return ['penalty_amount' => 0, 'days_overdue' => 0, 'penalty_percentage' => 0];
        }

        // Calculate due date (end of the bill month)
        $dueDate = \Carbon\Carbon::create($bill->bill_year, $bill->bill_month)->endOfMonth();
        $today = now();

        if ($today->lte($dueDate)) {
            return ['penalty_amount' => 0, 'days_overdue' => 0, 'penalty_percentage' => 0];
        }

        $daysOverdue = $today->diffInDays($dueDate);

        // Check if grace period has passed
        if ($daysOverdue <= $setting->grace_days) {
            return ['penalty_amount' => 0, 'days_overdue' => $daysOverdue, 'penalty_percentage' => 0];
        }

        // Calculate penalty on the outstanding balance
        $penaltyAmount = ($bill->balance * $setting->penalty_percentage) / 100;

        return [
            'penalty_amount' => round($penaltyAmount, 2),
            'days_overdue' => $daysOverdue,
            'penalty_percentage' => $setting->penalty_percentage,
        ];
    }
}
