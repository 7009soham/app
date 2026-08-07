<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TaxPayment extends Model
{
    use \App\Models\Concerns\Searchable;

    protected array $searchable = ['transaction_id', 'citizen_phone', 'provider_transaction_id'];

    protected array $transliterates = ['citizen_name' => 'citizen_name_roman'];

    protected static array $columnCache = [];

    protected $fillable = [
        'transaction_id',
        'citizen_id',
        'citizen_name',
        'citizen_phone',
        'citizen_address',
        'tax_type',
        'tax_type_id',
        'record_id',
        'amount',
        'period_type',
        'period_start',
        'period_end',
        'status',
        'payment_status',
        'payment_method',
        'provider_transaction_id',
        'phonepe_transaction_id',
        'failure_reason',
        'payment_data',
        'payment_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_data' => 'array',
        'payment_response' => 'array',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->transaction_id) {
                $payment->transaction_id = 'GP' . strtoupper(Str::random(10)) . time();
            }
        });
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class);
    }

    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class);
    }

    /**
     * Get the related tax record (water or property)
     */
    public function getTaxRecordAttribute()
    {
        if ($this->tax_type === 'water_tax' && $this->record_id) {
            return WaterTaxRecord::find($this->record_id);
        } elseif ($this->tax_type === 'property_tax' && $this->record_id) {
            return PropertyTaxRecord::find($this->record_id);
        }
        return null;
    }

    public function scopeCompleted($query)
    {
        if ($this->hasTaxPaymentsColumn('status')) {
            $query->where('status', 'success');
        }

        return $query->where('payment_status', 'completed')
            ->whereNotNull('paid_at');
    }

    public function scopePending($query)
    {
        return $query->where(function ($q) {
            if ($this->hasTaxPaymentsColumn('status')) {
                $q->where('status', 'pending')
                    ->orWhere('payment_status', 'pending');
                return;
            }

            $q->where('payment_status', 'pending');
        });
    }

    public function scopeFailed($query)
    {
        return $query->where(function ($q) {
            if ($this->hasTaxPaymentsColumn('status')) {
                $q->where('status', 'failed')
                    ->orWhere('payment_status', 'failed');
                return;
            }

            $q->where('payment_status', 'failed');
        });
    }

    public function markAsCompleted(string $providerTransactionId, array $response = []): void
    {
        $updateData = [
            'payment_status' => 'completed',
            'provider_transaction_id' => $providerTransactionId,
            'phonepe_transaction_id' => $providerTransactionId,
            'payment_response' => $response,
            'payment_data' => array_merge($this->payment_data ?? [], [
                'completed_at' => now()->toDateTimeString(),
            ]),
            'paid_at' => now(),
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $updateData['status'] = 'success';
        }

        $this->update($updateData);
    }

    public function markAsFailed(array $response = [], ?string $failureReason = null): void
    {
        $updateData = [
            'payment_status' => 'failed',
            'payment_response' => $response,
            'payment_data' => array_merge($this->payment_data ?? [], [
                'failed_at' => now()->toDateTimeString(),
            ]),
        ];

        if ($this->hasTaxPaymentsColumn('status')) {
            $updateData['status'] = 'failed';
        }

        if ($this->hasTaxPaymentsColumn('failure_reason')) {
            $updateData['failure_reason'] = $failureReason;
        }

        $this->update($updateData);
    }

    private function hasTaxPaymentsColumn(string $column): bool
    {
        if (!array_key_exists($column, self::$columnCache)) {
            self::$columnCache[$column] = Schema::hasColumn('tax_payments', $column);
        }

        return self::$columnCache[$column];
    }

    /**
     * Get human-readable tax type name
     */
    public function getTaxTypeNameAttribute(): string
    {
        return match ($this->tax_type) {
            'water_tax' => 'Water Tax',
            'property_tax' => 'Property Tax',
            default => ucfirst(str_replace('_', ' ', $this->tax_type ?? 'Unknown')),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        $status = $this->status ?? $this->payment_status ?? 'pending';
        return match ($status) {
            'success', 'completed' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            default => 'gray',
        };
    }
}

