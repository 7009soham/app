<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonthlyTaxBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'tax_type',
        'record_id',
        'customer_no',
        'customer_name',
        'bill_year',
        'bill_month',
        'bill_amount',
        'paid_amount',
        'balance',
        'status',
        'payment_method',
        'due_date',
        'due_reminder_sent_at',
        'paid_date',
        'marked_by',
        'remarks',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'due_reminder_sent_at' => 'datetime',
        'paid_date' => 'date',
        'bill_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function citizen()
    {
        return $this->belongsTo(Citizen::class);
    }

    public function marker()
    {
        return $this->belongsTo(Admin::class, 'marked_by');
    }

    public function getMonthNameAttribute()
    {
        return Carbon::create(null, $this->bill_month)->format('F');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->orWhere('status', 'partial')->orWhere('status', 'overdue');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
