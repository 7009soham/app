<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'avatar',
        'last_login_at',
        'last_login_ip',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Null-safe on purpose. role_id is nullable, and an admin whose role was
     * deleted would otherwise fatal here, which in a permission check means the
     * request dies rather than being refused. Absent role means no rights.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role?->type === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role?->type, ['superadmin', 'admin'], true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // An inactive role grants nothing, even if the admin row is active.
        if (!$this->role || !$this->role->is_active) {
            return false;
        }

        $permissions = $this->role->permissions ?? [];

        return in_array($permission, (array) $permissions, true);
    }
}
