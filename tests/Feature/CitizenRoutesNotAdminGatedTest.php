<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The admin permission gate must never sit on a citizen route.
 *
 * When it did, the effect was silent and severe: AdminCan redirects to the
 * admin login when no admin is authenticated, so a citizen who logged in was
 * bounced from their own dashboard to the staff login form. The portal looked
 * to them like it had rejected their login.
 *
 * The gates were applied by a script that walked routes/web.php and never
 * reset its "inside the admin group" flag, so citizen routes whose names
 * happened to match an admin permission key (dashboard, grievances.index)
 * picked one up. This pins the invariant rather than the script.
 */
class CitizenRoutesNotAdminGatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_citizen_route_carries_an_admin_permission_gate(): void
    {
        $offenders = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName() ?? '';
            $uri = $route->uri();

            $isCitizen = str_starts_with($name, 'citizen.') || str_starts_with($uri, 'citizen/');

            if (!$isCitizen) {
                continue;
            }

            foreach ($route->gatherMiddleware() as $middleware) {
                if (is_string($middleware) && str_starts_with($middleware, 'admin.can')) {
                    $offenders[] = $name . ' (' . $uri . ') -> ' . $middleware;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Citizen routes are gated on an admin permission, which redirects citizens to the staff login:\n"
                . implode("\n", $offenders)
        );
    }

    /** The mirror of the above: admin routes must all still be gated. */
    public function test_every_admin_route_still_carries_a_gate(): void
    {
        $ungated = [];

        // Login and logout are reachable before an admin exists, by definition.
        $exempt = ['admin.login', 'admin.login.submit', 'admin.logout'];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName() ?? '';

            if (!str_starts_with($name, 'admin.') || in_array($name, $exempt, true)) {
                continue;
            }

            $middleware = $route->gatherMiddleware();

            $hasGate = collect($middleware)
                ->filter(fn ($m) => is_string($m))
                ->contains(fn ($m) => str_starts_with($m, 'admin.can'));

            if (!$hasGate) {
                $ungated[] = $name;
            }
        }

        $this->assertSame([], $ungated, 'Ungated admin routes: ' . implode(', ', $ungated));
    }

    /** A citizen route must be reachable without an admin session. */
    public function test_the_citizen_login_page_does_not_redirect_to_the_admin_form(): void
    {
        $response = $this->get(route('citizen.login'));

        $response->assertOk();
        $response->assertDontSee(route('admin.login'), false);
    }
}
