<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level authorisation for the admin area.
 *
 * admin.auth only proves who you are. Until this existed nothing proved what
 * you were allowed to do: 15 of 20 admin controllers carried no permission
 * check at all, so any account that could log in could rewrite payment gateway
 * credentials, edit its own role's permissions and permanently delete tax
 * records. Hiding menu items is not access control.
 *
 * Deny by default. A route with no permission argument is refused rather than
 * allowed, so a mistake in the route file fails closed.
 */
class AdminCan
{
    public function handle(Request $request, Closure $next, ?string ...$permissions): Response
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $permissions = array_values(array_filter($permissions));

        if ($permissions === []) {
            Log::error('Admin route declared no permission; refusing.', [
                'route' => $request->route()?->getName(),
            ]);

            abort(403, 'This action is not configured.');
        }

        // Any one of the listed permissions is enough, which lets a screen that
        // serves two roles (say water and property staff) name both.
        foreach ($permissions as $permission) {
            if ($admin->hasPermission($permission)) {
                return $next($request);
            }
        }

        Log::warning('Admin permission denied.', [
            'admin_id' => $admin->id,
            'role' => $admin->role?->slug,
            'needed' => $permissions,
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
        ]);

        abort(403, 'You do not have permission to perform this action.');
    }
}
