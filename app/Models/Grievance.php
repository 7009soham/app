<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Grievance extends Model
{
    protected $fillable = [
        'ticket_no',
        'citizen_id',
        'name',
        'phone',
        'email',
        'address',
        'category',
        'description',
        'image',
        'latitude',
        'longitude',
        'location_address',
        'status',
        'priority',
        'admin_remarks',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'resolved_at' => 'datetime',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($grievance) {
            if (!$grievance->ticket_no) {
                $year = date('Y');
                $lastGrievance = static::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
                $nextNumber = $lastGrievance ? (intval(substr($lastGrievance->ticket_no, -4)) + 1) : 1;
                $grievance->ticket_no = 'GRV-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the admin assigned to this grievance
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    /**
     * Get the citizen who submitted this grievance
     */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class, 'citizen_id');
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'water_leakage' => 'Water Leakage',
            'road_damage' => 'Road Damage',
            'electricity' => 'Electricity Issue',
            'sanitation' => 'Sanitation',
            'drainage' => 'Drainage Problem',
            'streetlight' => 'Street Light',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Mark as in progress
     */
    public function markAsInProgress(?int $adminId = null, ?string $remarks = null): void
    {
        $this->update([
            'status' => 'in_progress',
            'assigned_to' => $adminId,
            'admin_remarks' => $remarks,
        ]);
    }

    /**
     * Mark as resolved
     */
    public function markAsResolved(?string $remarks = null): void
    {
        $this->update([
            'status' => 'resolved',
            'admin_remarks' => $remarks,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected(?string $remarks = null): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_remarks' => $remarks,
        ]);
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            'water_leakage' => 'Water Leakage',
            'road_damage' => 'Road Damage',
            'electricity' => 'Electricity Issue',
            'sanitation' => 'Sanitation',
            'drainage' => 'Drainage Problem',
            'streetlight' => 'Street Light',
            'other' => 'Other',
        ];
    }
}
