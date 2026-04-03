<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'causer_type',
        'causer_id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
