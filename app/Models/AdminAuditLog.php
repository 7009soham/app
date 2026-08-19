<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'admin_id', 'admin_email', 'admin_role', 'action',
        'subject_type', 'subject_id', 'subject_label',
        'before', 'after', 'ip', 'user_agent',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    /** Anything whose name suggests a secret is stored as a marker, never a value. */
    private const SECRET_HINTS = ['salt', 'key', 'secret', 'password', 'token', 'merchant_key'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Record an administrative action.
     *
     * Never throws: an audit failure must not take down the action being
     * audited, but it must be visible in the application log if it happens.
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?array $before = null,
        ?array $after = null,
        ?string $label = null
    ): void {
        try {
            $admin = Auth::guard('admin')->user();
            $request = request();

            static::create([
                'admin_id' => $admin?->id,
                'admin_email' => $admin?->email,
                'admin_role' => $admin?->role?->slug,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => $label,
                'before' => $before === null ? null : static::mask($before),
                'after' => $after === null ? null : static::mask($after),
                'ip' => $request?->ip(),
                'user_agent' => substr((string) $request?->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            Log::error('Audit write failed: ' . $e->getMessage(), ['action' => $action]);
        }
    }

    /**
     * Replace secret values with a marker that still shows whether the value
     * changed, so a salt rotation is visible without the salt being readable.
     */
    public static function mask(array $data): array
    {
        foreach ($data as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                continue;
            }

            foreach (self::SECRET_HINTS as $hint) {
                if (str_contains(strtolower((string) $key), $hint)) {
                    $data[$key] = $value === null || $value === ''
                        ? '(empty)'
                        : '(set, sha256:' . substr(hash('sha256', (string) $value), 0, 12) . ')';
                    break;
                }
            }
        }

        return $data;
    }
}
