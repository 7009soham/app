<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTaxRecord extends Model
{
    protected $fillable = [
        'a_no',
        'customer_no',
        'property_no',
        'property_type',
        'customer_name',
        'previous_house_tax',
        'previous_electricity_tax',
        'previous_health_tax',
        'previous_total',
        'current_house_tax',
        'current_electricity_tax',
        'current_health_tax',
        'current_total',
        'balance',
        'phone',
        'aadhaar_no',
        'citizen_id',
        'demand_id',
    ];

    protected $casts = [
        'previous_house_tax' => 'decimal:2',
        'previous_electricity_tax' => 'decimal:2',
        'previous_health_tax' => 'decimal:2',
        'previous_total' => 'decimal:2',
        'current_house_tax' => 'decimal:2',
        'current_electricity_tax' => 'decimal:2',
        'current_health_tax' => 'decimal:2',
        'current_total' => 'decimal:2',
        'balance' => 'decimal:2',
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
