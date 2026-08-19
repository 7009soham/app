<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\PropertyTaxRecord;
use App\Models\Role;
use App\Models\TaxPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The four critical findings from the audit.
 *
 * admin.auth proved identity and nothing else, so any account that could log in
 * could reach every function: rewrite payment gateway credentials, grant its own
 * role every permission, and permanently delete tax records leaving no trace.
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeRole(string $slug, string $type, array $permissions): Role
    {
        return Role::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'type' => $type,
            'permissions' => $permissions,
            'is_active' => true,
        ]);
    }

    private function makeAdmin(Role $role): Admin
    {
        return Admin::create([
            'name' => 'Staff ' . $role->slug,
            'email' => $role->slug . '@example.test',
            'password' => bcrypt('secret-password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    /** A data-entry clerk, modelled on the two real roles in production. */
    private function clerk(): Admin
    {
        return $this->makeAdmin($this->makeRole('data-entry-water', 'admin', [
            'dashboard.view', 'citizens.view', 'citizens.create', 'citizens.edit',
            'water_tax.view', 'water_tax.manage', 'water_tax.export',
        ]));
    }

    private function superAdmin(): Admin
    {
        return $this->makeAdmin($this->makeRole('superadmin', 'superadmin', []));
    }

    private function record(array $attributes = []): PropertyTaxRecord
    {
        static $n = 0;
        $n++;

        return PropertyTaxRecord::create(array_merge([
            'a_no' => $n,
            'customer_no' => 'C-' . $n,
            'property_no' => 'P-' . $n,
            'property_type' => 'Default',
            'customer_name' => 'Owner ' . $n,
            'balance' => 0,
        ], $attributes));
    }

    // C1 ------------------------------------------------------------------

    public function test_c1_a_clerk_cannot_reach_functions_outside_their_role(): void
    {
        $this->actingAs($this->clerk(), 'admin');

        foreach ([
            'admin.settings.index',
            'admin.settings.payment',
            'admin.roles.index',
            'admin.admins.index',
            'admin.grievances.index',
            'admin.analytics.index',
            'admin.tax-rate-adjustment.index',
        ] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_c1_a_clerk_keeps_the_access_their_job_needs(): void
    {
        $this->actingAs($this->clerk(), 'admin');

        $this->get(route('admin.water-tax.index'))->assertOk();
        $this->get(route('admin.citizens.index'))->assertOk();
    }

    public function test_c1_a_super_admin_still_reaches_everything(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        $this->get(route('admin.settings.payment'))->assertOk();
        $this->get(route('admin.roles.index'))->assertOk();
    }

    /**
     * Defensive rather than reachable: admins.role_id is NOT NULL and cascades
     * on role delete, so an admin cannot currently end up without a role. The
     * check still matters because in a permission gate a null role must mean
     * "no rights", not a fatal error that takes the request down.
     */
    public function test_c1_a_roleless_admin_grants_no_permission(): void
    {
        $this->assertFalse((new Admin)->hasPermission('water_tax.view'));
        $this->assertFalse((new Admin)->isSuperAdmin());
    }

    public function test_c1_an_inactive_role_grants_nothing(): void
    {
        $clerk = $this->clerk();
        $clerk->role->update(['is_active' => false]);

        $this->actingAs($clerk->fresh(), 'admin')
            ->get(route('admin.water-tax.index'))
            ->assertForbidden();
    }

    // C2 ------------------------------------------------------------------

    public function test_c2_a_clerk_cannot_edit_roles_at_all(): void
    {
        $clerk = $this->clerk();

        $this->actingAs($clerk, 'admin')
            ->put(route('admin.roles.update', $clerk->role), [
                'name' => 'Data Entry Water',
                'type' => 'admin',
                'permissions' => ['settings.payment', 'admins.create'],
            ])
            ->assertForbidden();

        $this->assertNotContains('settings.payment', $clerk->role->fresh()->permissions);
    }

    /** Even an admin who may edit roles cannot edit the one granting their own rights. */
    public function test_c2_nobody_can_edit_their_own_role(): void
    {
        $manager = $this->makeAdmin($this->makeRole('manager', 'admin', [
            'dashboard.view', 'roles.view', 'roles.edit',
        ]));

        $this->actingAs($manager, 'admin')
            ->put(route('admin.roles.update', $manager->role), [
                'name' => 'Manager',
                'type' => 'admin',
                'permissions' => ['dashboard.view', 'roles.view', 'roles.edit', 'settings.payment'],
            ])
            ->assertRedirect();

        $this->assertNotContains(
            'settings.payment',
            $manager->role->fresh()->permissions,
            'An admin escalated their own role.'
        );
    }

    /** You cannot mint a permission you do not hold yourself. */
    public function test_c2_an_admin_cannot_grant_beyond_their_own_rights(): void
    {
        $manager = $this->makeAdmin($this->makeRole('manager', 'admin', [
            'dashboard.view', 'roles.view', 'roles.edit',
        ]));
        $other = $this->makeRole('junior', 'employee', ['dashboard.view']);

        $this->actingAs($manager, 'admin')
            ->put(route('admin.roles.update', $other), [
                'name' => 'Junior',
                'type' => 'employee',
                'permissions' => ['dashboard.view', 'settings.payment'],
            ])
            ->assertRedirect();

        $this->assertNotContains('settings.payment', $other->fresh()->permissions);
    }

    // C3 ------------------------------------------------------------------

    public function test_c3_a_clerk_cannot_write_payment_credentials(): void
    {
        $this->actingAs($this->clerk(), 'admin')
            ->put(route('admin.settings.payment.update'), [
                'payu_water_merchant_id' => 'ATTACKER',
                'payu_water_merchant_key' => 'attacker-key',
            ])
            ->assertForbidden();
    }

    // C4 ------------------------------------------------------------------

    public function test_c4_a_clerk_cannot_delete_a_tax_record(): void
    {
        $record = $this->record();

        $this->actingAs($this->clerk(), 'admin')
            ->delete(route('admin.property-tax.destroy', $record))
            ->assertForbidden();

        $this->assertNotNull(PropertyTaxRecord::find($record->id));
    }

    public function test_c4_a_record_with_an_outstanding_balance_is_not_deletable(): void
    {
        $record = $this->record(['balance' => 4500]);

        $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.property-tax.destroy', $record))
            ->assertRedirect();

        $this->assertNotNull(PropertyTaxRecord::find($record->id), 'Live liability was deleted.');
    }

    public function test_c4_a_record_with_a_successful_payment_is_not_deletable(): void
    {
        $record = $this->record(['balance' => 0]);

        $taxType = \App\Models\TaxType::firstOrCreate(
            ['slug' => 'property-tax'],
            ['name' => 'Property Tax', 'is_active' => true]
        );

        TaxPayment::create([
            'transaction_id' => 'TXNTEST1',
            'citizen_name' => 'Owner',
            'citizen_phone' => '9425551234',
            'tax_type_id' => $taxType->id,
            'tax_type' => 'property_tax',
            'record_id' => $record->id,
            'amount' => 100,
            'period_type' => 'yearly',
            'period_start' => '2026-04-01',
            'period_end' => '2027-03-31',
            'status' => 'success',
        ]);

        $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.property-tax.destroy', $record))
            ->assertRedirect();

        $this->assertNotNull(PropertyTaxRecord::find($record->id), 'Evidence of collection was deleted.');
    }

    public function test_c4_a_permitted_delete_is_soft_and_audited(): void
    {
        $record = $this->record(['balance' => 0]);

        $this->actingAs($this->superAdmin(), 'admin')
            ->delete(route('admin.property-tax.destroy', $record))
            ->assertRedirect();

        $this->assertNull(PropertyTaxRecord::find($record->id), 'Should be hidden from normal queries.');
        $this->assertNotNull(
            PropertyTaxRecord::withTrashed()->find($record->id),
            'Deletion must be recoverable, not permanent.'
        );

        $log = AdminAuditLog::where('action', 'property_tax.delete')->first();
        $this->assertNotNull($log, 'The deletion left no audit trail.');
        $this->assertSame($record->id, (int) $log->subject_id);
    }

    /** Secrets must never be readable from the audit trail. */
    public function test_c4_audit_masks_secret_values(): void
    {
        $masked = AdminAuditLog::mask([
            'payu_water_merchant_id' => 'VISIBLE123',
            'payu_water_merchant_salt' => 'super-secret-salt',
            'phonepe_salt_key' => 'another-secret',
        ]);

        $this->assertSame('VISIBLE123', $masked['payu_water_merchant_id']);
        $this->assertStringNotContainsString('super-secret-salt', json_encode($masked));
        $this->assertStringNotContainsString('another-secret', json_encode($masked));
        $this->assertStringStartsWith('(set, sha256:', $masked['payu_water_merchant_salt']);
    }
}
