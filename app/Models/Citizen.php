<?php

namespace App\Models;

use App\Mail\CitizenEmailLinkedMail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Citizen extends Authenticatable
{
    use \App\Models\Concerns\Searchable;

    protected array $searchable = ['phone', 'customer_no', 'email'];

    protected array $transliterates = ['name' => 'name_roman'];

    use Notifiable;

    protected static function booted(): void
    {
        static::created(function (self $citizen): void {
            if (!empty($citizen->email)) {
                $citizen->sendEmailLinkedNotification();
            }
        });

        static::updated(function (self $citizen): void {
            if (!$citizen->wasChanged('email') || empty($citizen->email)) {
                return;
            }

            $previousEmail = trim((string) $citizen->getOriginal('email'));
            $currentEmail = trim((string) $citizen->email);

            // If this is effectively the same email (e.g., whitespace/casing changes), do not resend.
            if (strcasecmp($previousEmail, $currentEmail) === 0) {
                return;
            }

            $citizen->sendEmailLinkedNotification();
        });
    }

    protected $fillable = [
        'customer_no',
        'name',
        'phone',
        'email',
        'email_verified_at',
        'address',
        'aadhar_card',
        'demand_id',
        'is_active',
        'phone_verified_at',
        'otp',
        'otp_expires_at',
        'otp_sent_at',
        'banner_dismissed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'otp_sent_at' => 'datetime',
        'banner_dismissed' => 'boolean',
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
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $otp): bool
    {
        if (empty($this->otp)) {
            return false;
        }

        $storedOtp = (string) $this->otp;
        $isHashedOtp = str_starts_with($storedOtp, '$2y$') || str_starts_with($storedOtp, '$argon2');

        $isValid = $isHashedOtp ? Hash::check($otp, $storedOtp) : hash_equals($storedOtp, $otp);
        if (!$isValid) {
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

    /**
     * Send confirmation email when citizen email is linked/updated.
     */
    private function sendEmailLinkedNotification(): void
    {
        try {
            Mail::to($this->email)->send(new CitizenEmailLinkedMail([
                'citizen_name' => $this->name,
                'citizen_phone' => $this->phone,
                'customer_no' => $this->customer_no,
                'linked_at' => now(),
            ]));
        } catch (\Throwable $e) {
            Log::error('Failed to send citizen email linking confirmation.', [
                'citizen_id' => $this->id,
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
