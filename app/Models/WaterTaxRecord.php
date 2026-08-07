<?php

namespace App\Models;

use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterTaxRecord extends Model
{
    use Searchable;

    protected array $searchable = ['customer_no', 'a_no', 'bill_no', 'receipt_no', 'phone'];

    protected array $transliterates = ['customer_name' => 'customer_name_roman'];

    protected $fillable = [
        'a_no',
        'customer_no',
        'customer_name',
        'monthly_bill',
        'period',
        'balance',
        'oversize_charge',
        'bill_no',
        'receipt_no',
        'payment_date',
        'amount_paid',
        'shera',
        'phone',
        'citizen_id',
        'demand_id',
    ];

    protected $casts = [
        'monthly_bill' => 'decimal:2',
        'balance' => 'decimal:2',
        'oversize_charge' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the citizen that owns this record
     */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class);
    }

    /**
     * Check if there's a pending balance
     */
    public function hasPendingBalance(): bool
    {
        return $this->balance > 0;
    }

    public function getTotalDueAttribute(): float
    {
        return $this->balance;
    }

    /**
     * Scope for records with pending balance
     */
    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * Scope for records by customer number
     */
    public function scopeByCustomer($query, $customerNo)
    {
        return $query->where('customer_no', $customerNo);
    }

    /**
     * Scope for records by phone
     */
    public function scopeByPhone($query, $phone)
    {
        return $query->where('phone', $phone);
    }
}
