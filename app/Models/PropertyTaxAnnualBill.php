<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyTaxAnnualBill extends Model
{
    use HasFactory;

    protected $table = 'property_tax_annual_bills';

    protected $fillable = [
        'citizen_id',
        'record_id',
        'customer_no',
        'customer_name',
        'financial_year',
        'bill_period_start',
        'bill_period_end',
        'house_tax',
        'electricity_tax',
        'health_tax',
        'previous_balance',
        'bill_amount',
        'paid_amount',
        'balance',
        'status',
        'payment_method',
        'due_date',
        'due_reminder_sent_at',
        'paid_date',
        'bill_no',
        'transaction_id',
        'marked_by',
        'remarks',
    ];

    protected $casts = [
        'bill_period_start'  => 'date',
        'bill_period_end'    => 'date',
        'due_date'           => 'date',
        'due_reminder_sent_at' => 'datetime',
        'paid_date'          => 'date',
        'house_tax'          => 'decimal:2',
        'electricity_tax'    => 'decimal:2',
        'health_tax'         => 'decimal:2',
        'previous_balance'   => 'decimal:2',
        'bill_amount'        => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'balance'            => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function citizen()
    {
        return $this->belongsTo(Citizen::class);
    }

    public function propertyTaxRecord()
    {
        return $this->belongsTo(PropertyTaxRecord::class, 'record_id');
    }

    public function marker()
    {
        return $this->belongsTo(Admin::class, 'marked_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Returns the current financial year string, e.g. "2025-26"
     * Financial year: April 1 – March 31
     */
    public static function currentFinancialYear(): string
    {
        $now = Carbon::now();
        // If month is Jan-Mar, financial year started last calendar year
        if ($now->month <= 3) {
            return ($now->year - 1) . '-' . substr($now->year, -2);
        }
        return $now->year . '-' . substr($now->year + 1, -2);
    }

    /**
     * Returns the start date (April 1) for a given financial year string
     */
    public static function financialYearStart(string $fy): Carbon
    {
        $startYear = (int) explode('-', $fy)[0];
        return Carbon::create($startYear, 4, 1);
    }

    /**
     * Returns the end date (March 31) for a given financial year string
     */
    public static function financialYearEnd(string $fy): Carbon
    {
        $startYear = (int) explode('-', $fy)[0];
        return Carbon::create($startYear + 1, 3, 31);
    }

    /**
     * Human-readable label, e.g. "FY 2025-26 (Apr 2025 – Mar 2026)"
     */
    public function getFinancialYearLabelAttribute(): string
    {
        return 'FY ' . $this->financial_year
            . ' (' . $this->bill_period_start->format('M Y')
            . ' to ' . $this->bill_period_end->format('M Y') . ')';
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForFinancialYear($query, string $fy)
    {
        return $query->where('financial_year', $fy);
    }
}
