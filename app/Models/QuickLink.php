<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickLink extends Model
{
    protected $fillable = [
        'title',
        'url',
        'icon',
        'location',
        'order',
        'is_active',
        'open_new_tab',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_new_tab' => 'boolean',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeHeader($query)
    {
        return $query->where('location', 'header');
    }

    public function scopeFooter($query)
    {
        return $query->where('location', 'footer');
    }
}
