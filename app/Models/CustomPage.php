<?php

namespace App\Models;

use App\Helpers\RichText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'icon',
        'summary',
        'content',
        'is_published',
        'order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Content is sanitised on the way in, not on the way out, so the stored
     * value is always safe and every render site cannot forget to clean it.
     */
    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = RichText::sanitize($value);
    }

    public function setSlugAttribute($value): void
    {
        $this->attributes['slug'] = Str::slug((string) $value);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('title');
    }

    public function getUrlAttribute(): string
    {
        return url('/page/' . $this->slug);
    }

    public function getExcerptAttribute(): string
    {
        return $this->summary !== null && $this->summary !== ''
            ? $this->summary
            : RichText::excerpt($this->content);
    }

    /**
     * Build a slug that does not collide with an existing page.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
