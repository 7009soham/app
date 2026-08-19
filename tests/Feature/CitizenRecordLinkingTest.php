<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use App\Models\SiteSetting;
use App\Models\WaterTaxRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * How a verified phone number gets attached to the tax records it owns.
 *
 * Two defects lived here. Login linked a single record per tax type, found with
 * first(), so a citizen with several properties saw exactly one of them and any
 * bill raised against the others looked to them like it had vanished. And the
 * citizen row was created with firstOrCreate on a unique customer_no, while 54
 * customer numbers in the live property ledger are shared by two different
 * owners, so those citizens hit a duplicate-key error at login.
 */
class CitizenRecordLinkingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SiteSetting::updateOrCreate(['key' => 'firebase_enabled'], ['value' => '1']);
    }

    private function property(array $attributes = []): PropertyTaxRecord
    {
        static $n = 0;
        $n++;

        return PropertyTaxRecord::create(array_merge([
            'a_no' => $n,
            'customer_no' => 'C-' . $n,
            'property_no' => 'P-' . $n,
            'property_type' => 'Default',
            'customer_name' => 'Owner ' . $n,
            'balance' => 100 * $n,
        ], $attributes));
    }

    private function sendOtp(string $phone)
    {
        return $this->postJson(route('citizen.send-otp'), ['phone' => $phone]);
    }

    public function test_every_property_for_the_phone_is_linked_not_just_the_first(): void
    {
        $a = $this->property(['phone' => '9425551234']);
        $b = $this->property(['phone' => '9425551234']);
        $c = $this->property(['phone' => '9425551234']);

        $this->sendOtp('9425551234');

        $citizen = Citizen::where('phone', '9425551234')->first();
        $this->assertNotNull($citizen, 'The citizen should have been created.');

        foreach ([$a, $b, $c] as $record) {
            $this->assertSame(
                $citizen->id,
                $record->fresh()->citizen_id,
                "Record {$record->property_no} was left unlinked, so the citizen cannot see it."
            );
        }
    }

    public function test_records_stored_with_a_country_code_still_link(): void
    {
        $plain = $this->property(['phone' => '9425551234']);
        $prefixed = $this->property(['phone' => '+919425551234']);

        $this->sendOtp('9425551234');

        $citizen = Citizen::where('phone', '9425551234')->first();
        $this->assertSame($citizen->id, $plain->fresh()->citizen_id);
        $this->assertSame($citizen->id, $prefixed->fresh()->citizen_id, 'The +91 form was not matched.');
    }

    public function test_water_and_property_are_both_linked_for_the_same_phone(): void
    {
        $property = $this->property(['phone' => '9425551234']);
        $water = WaterTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'W-1',
            'customer_name' => 'Owner',
            'phone' => '9425551234',
            'monthly_bill' => 120,
            'balance' => 240,
            'period' => 'Apr 26 to Mar 27',
        ]);

        $this->sendOtp('9425551234');

        $citizen = Citizen::where('phone', '9425551234')->first();
        $this->assertSame($citizen->id, $property->fresh()->citizen_id);
        $this->assertSame($citizen->id, $water->fresh()->citizen_id);
    }

    /**
     * The live failure: the customer number on the tax record already belongs to
     * a different citizen, so creating this one with it violates the unique
     * index. The citizen must still get in.
     */
    public function test_a_taken_customer_no_does_not_break_login(): void
    {
        Citizen::create(['customer_no' => 'SHARED-1', 'name' => 'First Owner', 'phone' => '9000000001']);
        $record = $this->property(['phone' => '9425551234', 'customer_no' => 'SHARED-1']);

        $response = $this->sendOtp('9425551234');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $citizen = Citizen::where('phone', '9425551234')->first();
        $this->assertNotNull($citizen, 'Login must not fail because of a shared customer number.');
        $this->assertNull($citizen->customer_no, 'The colliding number must not be copied onto the new citizen.');
        $this->assertSame($citizen->id, $record->fresh()->citizen_id);

        // The original owner keeps theirs.
        $this->assertSame('SHARED-1', Citizen::where('phone', '9000000001')->first()->customer_no);
    }

    /**
     * A shared customer number must never hand one citizen another's property.
     * Linking is by verified phone only, never by customer_no.
     */
    public function test_a_shared_customer_no_does_not_leak_another_citizens_record(): void
    {
        $mine = $this->property(['phone' => '9425551234', 'customer_no' => 'SHARED-9']);
        $theirs = $this->property(['phone' => '9888800000', 'customer_no' => 'SHARED-9']);

        $this->sendOtp('9425551234');

        $citizen = Citizen::where('phone', '9425551234')->first();
        $this->assertSame($citizen->id, $mine->fresh()->citizen_id);
        $this->assertNull(
            $theirs->fresh()->citizen_id,
            'A record belonging to a different phone was attached via the shared customer number.'
        );
    }

    public function test_an_unknown_phone_is_refused(): void
    {
        $this->sendOtp('9999999999')
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    }
}
