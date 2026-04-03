<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_type',
        'percentage',
        'apply_to',
        'filters',
        'affected_records_count',
        'performed_by',
        'is_reverted',
        'reverted_at',
        'reverted_by',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_reverted' => 'boolean',
        'reverted_at' => 'datetime',
        'filters' => 'array',
    ];

    public function performer()
    {
        return $this->belongsTo(Admin::class, 'performed_by');
    }

    public function reverter()
    {
        return $this->belongsTo(Admin::class, 'reverted_by');
    }
}
