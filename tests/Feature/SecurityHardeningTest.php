<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $password = 'correct-horse'): Admin
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'type' => 'superadmin',
            'is_active' => true,
        ]);

        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt($password),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function attemptLogin(string $password): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.test',
            'password' => $password,
        ]);
    }

    public function test_admin_login_locks_out_after_five_failed_attempts(): void
    {
        $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin('wrong-password-' . $i)
                ->assertSessionHasErrors('email');
        }

        // The sixth attempt is refused before the password is even checked.
        $response = $this->attemptLogin('wrong-again');

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
    }

    public function test_lockout_blocks_even_the_correct_password(): void
    {
        $this->admin('correct-horse');

        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin('wrong-' . $i);
        }

        $this->attemptLogin('correct-horse');

        $this->assertGuest('admin');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
    }

    public function test_a_successful_login_clears_the_throttle(): void
    {
        $this->admin('correct-horse');

        $this->attemptLogin('wrong-once');
        $this->attemptLogin('wrong-twice');

        $this->attemptLogin('correct-horse');
        $this->assertAuthenticated('admin');

        $this->post(route('admin.logout'));

        // Counter reset, so a fresh run of failures is allowed again.
        for ($i = 0; $i < 4; $i++) {
            $this->attemptLogin('wrong-' . $i)
                ->assertSessionHasErrors('email');
        }

        $this->assertStringNotContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
    }

    public function test_throttle_is_scoped_to_email_and_ip_not_email_alone(): void
    {
        $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin('wrong-' . $i);
        }

        // A different address must not inherit the attacker's lockout, or an
        // attacker could lock a real administrator out of their own account.
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->post(route('admin.login.submit'), [
                'email' => 'admin@example.test',
                'password' => 'correct-horse',
            ]);

        $this->assertAuthenticated('admin');
        $response->assertRedirect();
    }

    public function test_permissions_policy_header_is_present(): void
    {
        $this->get('/')->assertHeader('Permissions-Policy');
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertHeader('Strict-Transport-Security');
        $this->assertStringContainsString(
            'max-age=31536000',
            $response->headers->get('Strict-Transport-Security')
        );
    }

    public function test_hsts_is_not_sent_over_plain_http(): void
    {
        // Sending HSTS over http is meaningless and browsers ignore it.
        $this->get('http://localhost/')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_email_template_body_is_escaped_in_the_textarea(): void
    {
        $layout = file_get_contents(
            resource_path('views/admin/settings/notifications.blade.php')
        );

        $this->assertStringNotContainsString(
            '{!! old(',
            $layout,
            'Raw old() input can close the textarea and inject markup.'
        );
    }

    public function test_phonepe_checksum_uses_a_constant_time_comparison(): void
    {
        $service = file_get_contents(app_path('Services/PhonePeService.php'));

        $this->assertStringContainsString('hash_equals', $service);
        $this->assertStringNotContainsString(
            '$receivedChecksum === $expectedChecksum',
            $service
        );
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('admin-login:admin@example.test|127.0.0.1');
        parent::tearDown();
    }
}
