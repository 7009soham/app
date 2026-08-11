<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            // A row that exists with a NULL value must still yield the default.
            // Returning null here crashed any caller with a typed string
            // property - RazorpayService::$keyId threw a TypeError on the
            // citizen pay-bill page because razorpay_key_id was saved as NULL
            // rather than removed.
            if (!$setting || $setting->value === null) {
                return $default;
            }

            return $setting->value;
        });
    }

    public static function set(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
        Cache::forget("setting_{$key}");
    }

    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
