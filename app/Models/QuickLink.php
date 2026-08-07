<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickLink extends Model
{
    protected $fillable = [
        'title',
        'url',
        'custom_page_id',
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

    /**
     * The page whose content this link owns, when the content was written in
     * the link form rather than pointing at an existing URL.
     */
    public function customPage()
    {
        return $this->belongsTo(CustomPage::class);
    }

    public function ownsItsPage(): bool
    {
        return $this->custom_page_id !== null;
    }
}
