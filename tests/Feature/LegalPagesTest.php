<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every legal/policy page must be publicly reachable without authentication.
     */
    public static function legalRouteProvider(): array
    {
        return [
            'privacy policy' => ['privacy-policy', 'Privacy Policy'],
            'terms and conditions' => ['terms-conditions', 'Terms'],
            'refund policy' => ['refund-policy', 'Refund'],
            'disclaimer' => ['disclaimer', 'Disclaimer'],
            'accessibility statement' => ['accessibility-statement', 'Accessibility'],
            'copyright policy' => ['copyright-policy', 'Copyright'],
            'hyperlinking policy' => ['hyperlinking-policy', 'Hyperlinking'],
        ];
    }

    #[DataProvider('legalRouteProvider')]
    public function test_legal_page_is_publicly_accessible(string $routeName, string $expectedText): void
    {
        $response = $this->get(route($routeName));

        $response->assertOk();
        $response->assertSee($expectedText, false);
    }

    /**
     * The footer must link to every legal page, on every page of the site.
     * This is a GIGW compliance requirement.
     */
    public function test_footer_links_to_all_legal_pages(): void
    {
        $response = $this->get(route('privacy-policy'));

        $response->assertOk();

        foreach (array_keys(self::legalRouteProvider()) as $label) {
            [$routeName] = self::legalRouteProvider()[$label];
            $response->assertSee(route($routeName), false);
        }
    }

    /**
     * The privacy policy must reference the DPDP Act and name a Grievance Officer.
     */
    public function test_privacy_policy_covers_dpdp_act_requirements(): void
    {
        $response = $this->get(route('privacy-policy'));

        $response->assertOk();
        $response->assertSee('Digital Personal Data Protection Act, 2023', false);
        $response->assertSee('Data Principal', false);
        $response->assertSee('Grievance Officer', false);
    }
}
