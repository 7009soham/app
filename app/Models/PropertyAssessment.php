<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PropertyAssessment extends Model
{
    protected $fillable = [
        'assessment_type',
        'group_id',
        'sr_no',
        'property_number',
        'description',
        'owner_name',
        'tenant_name',
        'year_of_construction',
        'length',
        'width',
        'square_foot',
        'square_meter',
        'rr_rate_land',
        'rr_rate_building',
        'amount_with_depreciation',
        'total',
        'rate_of_education',
        'rate_of_bearable',
        'capital_value',
        'tax_rate',
        'house_tax',
        'light_tax',
        'health_tax',
        'grand_total',
        'financial_year',
        'record_id',
        'demand_id',
    ];

    protected $casts = [
        'length'                   => 'decimal:2',
        'width'                    => 'decimal:2',
        'square_foot'              => 'decimal:2',
        'square_meter'             => 'decimal:2',
        'rr_rate_land'             => 'decimal:2',
        'rr_rate_building'         => 'decimal:2',
        'amount_with_depreciation' => 'decimal:2',
        'total'                    => 'decimal:2',
        'rate_of_education'        => 'decimal:4',
        'rate_of_bearable'         => 'decimal:4',
        'capital_value'            => 'decimal:2',
        'tax_rate'                 => 'decimal:4',
        'house_tax'                => 'decimal:2',
        'light_tax'                => 'decimal:2',
        'health_tax'               => 'decimal:2',
        'grand_total'              => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────

    public function propertyTaxRecord(): BelongsTo
    {
        return $this->belongsTo(PropertyTaxRecord::class, 'record_id');
    }

    public function demand(): BelongsTo
    {
        return $this->belongsTo(Demand::class);
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeRegular($query)
    {
        return $query->where('assessment_type', 'regular');
    }

    public function scopeRented($query)
    {
        return $query->where('assessment_type', 'rented');
    }

    public function scopeExtended($query)
    {
        return $query->where('assessment_type', 'extended');
    }

    public function scopeFinancialYear($query, string $fy)
    {
        return $query->where('financial_year', $fy);
    }

    // ── Helpers ─────────────────────────────────────

    /**
     * Get all rows belonging to the same extended group.
     */
    public function getExtensionRows()
    {
        if (!$this->group_id) {
            return collect([$this]);
        }

        return static::where('group_id', $this->group_id)
            ->orderBy('sr_no')
            ->get();
    }

    /**
     * Check if this is the first (parent) row of an extended group.
     */
    public function isParentRow(): bool
    {
        if ($this->assessment_type !== 'extended' || !$this->group_id) {
            return true;
        }

        return static::where('group_id', $this->group_id)
            ->orderBy('id')
            ->value('id') === $this->id;
    }

    /**
     * Generate a unique group ID for extended assessments.
     */
    public static function generateGroupId(): string
    {
        return 'GRP-' . strtoupper(Str::random(8));
    }

    /**
     * Get assessment type label in Marathi.
     */
    public function getTypeMarathiAttribute(): string
    {
        return match ($this->assessment_type) {
            'regular'  => 'नियमित',
            'rented'   => 'भाडेकरू',
            'extended' => 'वाढीव',
            default    => $this->assessment_type,
        };
    }
}
