<?php

namespace Tests\Feature;

use App\Models\Citizen;
use App\Models\PropertyTaxRecord;
use App\Models\SiteSetting;
use App\Models\TaxPayment;
use App\Models\TaxType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * PayU posts its result from its own domain. The session cookie is
 * SameSite=lax, so the browser withholds it, and Laravel used to mint a fresh
 * session and Set-Cookie it over the citizen's own. The citizen was silently
 * logged out mid-payment, bounced to the login page after paying, and any
 * later submit from an open tab failed with 419 PAGE EXPIRED.
 */
class PayuReturnSessionTest extends TestCase
{
    use RefreshDatabase;

    private Citizen $citizen;
    private PropertyTaxRecord $record;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        SiteSetting::set('payu_enabled', '1', 'payment', 'boolean');
        SiteSetting::set('payu_property_merchant_key', 'PROPKEY', 'payment');
        SiteSetting::set('payu_property_merchant_salt', 'propsalt', 'payment');
        TaxType::firstOrCreate(['slug' => 'property-tax'], ['name' => 'Property Tax', 'is_active' => true]);

        $this->citizen = Citizen::create(['customer_no' => 'C-1', 'name' => 'Asha', 'phone' => '9425551234']);

        $this->record = PropertyTaxRecord::create([
            'a_no' => 1, 'customer_no' => 'C-1', 'property_no' => '1', 'property_type' => 'Default',
            'customer_name' => 'Asha', 'balance' => 1000, 'citizen_id' => $this->citizen->id,
        ]);
    }

    private function payment(): TaxPayment
    {
        return TaxPayment::create([
            'transaction_id' => 'TXNSESS1',
            'citizen_id' => $this->citizen->id,
            'citizen_name' => 'Asha',
            'citizen_phone' => '9425551234',
            'tax_type' => 'property_tax',
            'tax_type_id' => TaxType::where('slug', 'property-tax')->value('id'),
            'record_id' => $this->record->id,
            'amount' => 400,
            'period_type' => 'yearly',
            'period_start' => now()->startOfYear()->toDateString(),
            'period_end' => now()->endOfYear()->toDateString(),
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'payu',
            'payment_data' => ['tax_amount' => 400, 'convenience_fee' => 0],
        ]);
    }

    private function signed(string $status = 'success'): array
    {
        $r = [
            'status' => $status, 'txnid' => 'TXNSESS1', 'amount' => '400.00',
            'productinfo' => 'Property Tax', 'firstname' => 'Asha', 'email' => 'a@b.com',
            'udf1' => 'property', 'udf2' => (string) $this->record->id,
        ];

        $r['hash'] = strtolower(hash('sha512', implode('|', [
            'propsalt', $r['status'], '', '', '', '', '', '', '', '', $r['udf2'], $r['udf1'],
            $r['email'], $r['firstname'], $r['productinfo'], $r['amount'], $r['txnid'], 'PROPKEY',
        ])));

        return $r;
    }

    /**
     * The core regression: the return must not issue a session cookie, because
     * doing so overwrites the citizen's own and logs them out.
     */
    public function test_the_return_does_not_overwrite_the_citizens_session_cookie(): void
    {
        $this->payment();

        $response = $this->post(route('citizen.payment.payu-return'), $this->signed());

        $sessionCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertNull(
            $sessionCookie,
            'Setting a session cookie here replaces the logged-in one and signs the citizen out.'
        );
    }

    public function test_the_return_redirects_to_a_signed_result_page_not_an_auth_gated_one(): void
    {
        $this->payment();

        $location = $this->post(route('citizen.payment.payu-return'), $this->signed())
            ->headers->get('Location');

        $this->assertStringContainsString('/citizen/payment/result/', $location);
        // Signed, because the handler has no session to flash an outcome through.
        $this->assertStringContainsString('signature=', $location);
    }

    public function test_a_still_logged_in_citizen_reaches_the_result_page(): void
    {
        $payment = $this->payment();
        $this->post(route('citizen.payment.payu-return'), $this->signed());

        $url = URL::temporarySignedRoute('citizen.payment.result', now()->addMinutes(30), [
            'payment' => $payment->id, 'outcome' => 'success',
        ]);

        $this->actingAs($this->citizen, 'citizen')->get($url)
            ->assertOk()
            ->assertSee('Payment Successful')
            ->assertSee('TXNSESS1');
    }

    /**
     * If their session did lapse, they must still learn the outcome rather than
     * being bounced to a login form after paying.
     */
    public function test_a_logged_out_citizen_still_sees_the_outcome(): void
    {
        $payment = $this->payment();

        $url = URL::temporarySignedRoute('citizen.payment.result', now()->addMinutes(30), [
            'payment' => $payment->id, 'outcome' => 'success',
        ]);

        $this->get($url)->assertOk()->assertSee('Log in to view your records');
    }

    public function test_an_unsigned_result_url_is_rejected(): void
    {
        $payment = $this->payment();

        // Otherwise anyone could craft a page claiming a payment succeeded.
        $this->get(route('citizen.payment.result', ['payment' => $payment->id, 'outcome' => 'success']))
            ->assertForbidden();
    }

    public function test_a_logged_in_citizen_cannot_open_someone_elses_result(): void
    {
        $payment = $this->payment();
        $other = Citizen::create(['customer_no' => 'C-2', 'name' => 'Other', 'phone' => '9000000000']);

        $url = URL::temporarySignedRoute('citizen.payment.result', now()->addMinutes(30), [
            'payment' => $payment->id, 'outcome' => 'success',
        ]);

        $this->actingAs($other, 'citizen')->get($url)->assertForbidden();
    }

    public function test_a_cancellation_reaches_the_result_page_as_a_cancellation(): void
    {
        $this->payment();

        $location = $this->post(
            route('citizen.payment.payu-return'),
            array_merge($this->signed('failure'), ['unmappedstatus' => 'userCancelled'])
        )->headers->get('Location');

        $this->assertStringContainsString('outcome=cancelled', $location);

        $this->actingAs($this->citizen, 'citizen')->get($location)
            ->assertOk()
            ->assertSee('Payment Cancelled');
    }

    public function test_the_result_page_carries_the_official_identity(): void
    {
        SiteSetting::set('site_name', 'Neral Gram Panchayat', 'general');
        $payment = $this->payment();

        $url = URL::temporarySignedRoute('citizen.payment.result', now()->addMinutes(30), [
            'payment' => $payment->id, 'outcome' => 'success',
        ]);

        // Someone who has just paid must see whose site confirmed it.
        $this->get($url)->assertSee('Neral Gram Panchayat');
    }

    /** An expired signature must not still open the page. */
    public function test_an_expired_signature_is_rejected(): void
    {
        $payment = $this->payment();

        $url = URL::temporarySignedRoute('citizen.payment.result', now()->subMinute(), [
            'payment' => $payment->id, 'outcome' => 'success',
        ]);

        $this->get($url)->assertForbidden();
    }
}
