<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Citizen extends Authenticatable
{
    use Notifiable;
    protected $fillable = [
        'customer_no',
        'name',
        'phone',
        'email',
        'address',
        'aadhar_card',
        'demand_id',
        'is_active',
        'phone_verified_at',
        'otp',
        'otp_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'otp',
    ];

    /**
     * Get all water tax records for this citizen
     */
    public function waterTaxRecords(): HasMany
    {
        return $this->hasMany(WaterTaxRecord::class);
    }

    /**
     * Get all property tax records for this citizen
     */
    public function propertyTaxRecords(): HasMany
    {
        return $this->hasMany(PropertyTaxRecord::class);
    }

    /**
     * Get the demand for this citizen
     */
    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    /**
     * Get all grievances submitted by this citizen
     */
    public function grievances(): HasMany
    {
        return $this->hasMany(Grievance::class);
    }

    /**
     * Check if phone is verified
     */
    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * Mark phone as verified
     */
    public function markPhoneAsVerified(): void
    {
        $this->update([
            'phone_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Generate OTP for verification (fallback method)
     */
    public function generateOtp(): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $otp): bool
    {
        if ($this->otp !== $otp) {
            return false;
        }

        if ($this->otp_expires_at && $this->otp_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get total water tax balance
     */
    public function getTotalWaterTaxBalanceAttribute(): float
    {
        return $this->waterTaxRecords()->sum('balance') ?? 0;
    }

    /**
     * Get total property tax balance
     */
    public function getTotalPropertyTaxBalanceAttribute(): float
    {
        return $this->propertyTaxRecords()->sum('balance') ?? 0;
    }

    /**
     * Get total balance (water + property tax)
     */
    public function getTotalBalanceAttribute(): float
    {
        return $this->total_water_tax_balance + $this->total_property_tax_balance;
    }

    /**
     * Scope for active citizens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified citizens
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }
}
