<?php

namespace App\Models\Concerns;

use App\Helpers\Transliterate;
use Illuminate\Database\Eloquent\Builder;

/**
 * One search implementation for every admin listing.
 *
 * A model opts in by declaring:
 *
 *     protected array $searchable = ['property_no', 'phone'];
 *     protected array $transliterates = ['customer_name' => 'customer_name_roman'];
 *
 * Names are matched against both the stored Devanagari and a romanised copy,
 * so staff can type "soham" and find "सोहम". The romanised column is kept in
 * step automatically whenever the source attribute changes.
 */
trait Searchable
{
    public static function bootSearchable(): void
    {
        static::saving(function ($model) {
            foreach ($model->transliteratedColumns() as $source => $target) {
                // Only recompute when the name actually changed, so bulk
                // updates of unrelated fields stay cheap.
                if ($model->isDirty($source) || $model->{$target} === null) {
                    $model->{$target} = Transliterate::toSearchKey($model->{$source});
                }
            }
        });
    }

    public function searchableColumns(): array
    {
        return $this->searchable ?? [];
    }

    public function transliteratedColumns(): array
    {
        return $this->transliterates ?? [];
    }

    /**
     * Match $term against every searchable column, plus both the original and
     * romanised form of each transliterated column.
     *
     * A blank term is a no-op so controllers can call this unconditionally.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';
        $romanLike = '%' . Transliterate::toSearchKey($term) . '%';

        return $query->where(function (Builder $q) use ($like, $romanLike) {
            foreach ($this->searchableColumns() as $column) {
                $q->orWhere($column, 'like', $like);
            }

            foreach ($this->transliteratedColumns() as $source => $target) {
                // Devanagari typed directly still matches the original column.
                $q->orWhere($source, 'like', $like)
                  ->orWhere($target, 'like', $romanLike);
            }
        });
    }

    /**
     * Recompute the romanised columns for rows imported or edited outside the
     * model, used by the backfill migration and the artisan command.
     */
    public function refreshRomanisedColumns(): bool
    {
        $changed = false;

        foreach ($this->transliteratedColumns() as $source => $target) {
            $value = Transliterate::toSearchKey($this->{$source});

            if ($this->{$target} !== $value) {
                $this->{$target} = $value;
                $changed = true;
            }
        }

        return $changed;
    }
}
