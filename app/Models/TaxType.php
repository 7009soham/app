<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_rate',
        'quarterly_rate',
        'yearly_rate',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monthly_rate' => 'decimal:2',
        'quarterly_rate' => 'decimal:2',
        'yearly_rate' => 'decimal:2',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(TaxPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRateByPeriod(string $period): float
    {
        return match ($period) {
            'monthly' => $this->monthly_rate,
            'quarterly' => $this->quarterly_rate,
            'yearly' => $this->yearly_rate,
            default => 0,
        };
    }
}
