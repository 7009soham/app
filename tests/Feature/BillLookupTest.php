<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage lookup follows colorado.gov and panvelmc.org in leading with a
 * search box, but deliberately does not behave like Panvel's.
 *
 * Neral's property numbers are sequential Devanagari ("१२३/क", "१२३/क/१"), so an
 * open lookup returning owner and balance would let anyone walk the numbers and
 * map out the whole tax roll. Instead the box only carries a mobile number into
 * the existing OTP login, and nothing is disclosed until that phone is verified.
 */
class BillLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_offers_the_lookup(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find my tax bills')
            ->assertSee('name="phone"', false);
    }

    public function test_the_lookup_targets_the_otp_login_not_a_data_endpoint(): void
    {
        $this->get('/')->assertSee('action="' . route('citizen.login') . '"', false);
    }

    public function test_a_valid_mobile_is_carried_into_the_login_field(): void
    {
        $this->get(route('citizen.login', ['phone' => '9425551234']))
            ->assertOk()
            ->assertSee('value="9425551234"', false);
    }

    /** Garbage in the query string must never be echoed back into the page. */
    public function test_a_non_mobile_value_is_discarded(): void
    {
        foreach (['१२३/क', 'abcdefghij', '123', '<script>x</script>', '00000000000'] as $junk) {
            $this->get(route('citizen.login', ['phone' => $junk]))
                ->assertOk()
                ->assertDontSee('value="' . $junk . '"', false);
        }
    }

    public function test_an_indian_mobile_must_start_with_six_or_higher(): void
    {
        // 10 digits but not a real mobile prefix.
        $this->get(route('citizen.login', ['phone' => '1234567890']))
            ->assertOk()
            ->assertDontSee('value="1234567890"', false);
    }

    /**
     * The point of the whole design: submitting somebody else's number reveals
     * nothing, because the bill only appears after their OTP is verified.
     */
    public function test_submitting_a_number_discloses_no_citizen_data(): void
    {
        $citizen = Citizen::create([
            'customer_no' => 'C-001',
            'name' => 'सोहम तारे',
            'phone' => '9425551234',
        ]);

        PropertyTaxRecord::create([
            'a_no' => 1,
            'customer_no' => 'C-001',
            'property_no' => '१२३/क',
            'property_type' => 'Default',
            'customer_name' => 'सोहम तारे',
            'balance' => 8087.99,
            'citizen_id' => $citizen->id,
        ]);

        $response = $this->get(route('citizen.login', ['phone' => '9425551234']))->assertOk();

        $response->assertDontSee('सोहम तारे');
        $response->assertDontSee('8087.99');
        $response->assertDontSee('१२३/क');
    }

    public function test_the_lookup_is_translated_in_every_locale(): void
    {
        foreach (['en' => 'Find my tax bills', 'hi' => 'मेरे कर बिल देखें', 'mr' => 'माझी कर बिले पहा'] as $locale => $heading) {
            app()->setLocale($locale);

            $this->assertSame($heading, __('messages.find_my_bills'), "Missing translation for {$locale}.");
        }
    }

    public function test_the_lookup_input_can_shrink_on_a_narrow_phone(): void
    {
        $css = file_get_contents(public_path('css/app.css'));

        // A flex input keeps its intrinsic width without this and overflows.
        $this->assertMatchesRegularExpression('/\.lookup-input\s*\{[^}]*min-width:\s*0/s', $css);
    }
}
