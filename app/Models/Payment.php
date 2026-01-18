<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'tax_type',
        'bill_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'paid_at',
        'processed_by',
        'remarks',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function citizen()
    {
        return $this->belongsTo(Citizen::class);
    }

    public function bill()
    {
        return $this->belongsTo(MonthlyTaxBill::class, 'bill_id');
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }
}
