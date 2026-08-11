<?php

namespace Tests\Feature;

use App\Models\TaxType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PaymentController resolves a TaxType by slug before it will create a
 * payment. Production had none, so every online payment stopped with
 * "Tax Type configuration not found", and there is no admin screen to add one.
 */
class RequiredTaxTypesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * These exact slugs are what PaymentController looks up. Renaming one
     * breaks checkout for that tax head with a message that sounds like a
     * configuration mistake rather than a bug.
     */
    public function test_the_slugs_the_payment_flow_looks_up_exist(): void
    {
        foreach (['property-tax', 'water-tax'] as $slug) {
            $this->assertNotNull(
                TaxType::where('slug', $slug)->first(),
                "Missing tax type [{$slug}] - online payment for that head cannot start."
            );
        }
    }

    public function test_both_required_types_are_active(): void
    {
        $this->assertSame(
            2,
            TaxType::whereIn('slug', ['property-tax', 'water-tax'])->where('is_active', true)->count()
        );
    }

    /**
     * The seeder used to create the property row as "house-tax", which the
     * lookup never matched. Nothing should reintroduce that slug.
     */
    public function test_the_legacy_house_tax_slug_is_not_used(): void
    {
        $this->assertNull(TaxType::where('slug', 'house-tax')->first());
    }
}
