<?php

namespace Tests\Feature;

use App\Models\PropertyTaxRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The furniture that makes a citizen read this as a government portal rather
 * than a brand, plus the accessibility controls GIGW 3.0 expects to sit above
 * the masthead.
 *
 * Each assertion pins the thing whose absence was a defect, not the styling.
 */
class GovernmentMastheadTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_skip_link_is_the_first_focusable_element_and_has_a_target(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('class="skip-link" href="#main"', $html);

        // Without tabindex the skip link scrolls but cannot place focus, so
        // WCAG 2.4.1 is not actually satisfied.
        $this->assertStringContainsString('id="main" tabindex="-1"', $html);

        $bodyStart = strpos($html, '<body>');
        $skipAt = strpos($html, 'class="skip-link"');
        $navAt = strpos($html, 'class="nav"');

        $this->assertNotFalse($bodyStart);
        $this->assertLessThan($navAt, $skipAt, 'The skip link must precede the navigation.');
    }

    public function test_the_parent_government_is_named_in_both_scripts_with_lang_attributes(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<span lang="mr">महाराष्ट्र शासन</span>', $html);
        $this->assertStringContainsString('Government of Maharashtra', $html);
    }

    public function test_the_tricolour_rule_is_present_and_decorative(): void
    {
        $this->get('/')->assertOk()->assertSee('<div class="tricolour" aria-hidden="true">', false);
    }

    public function test_the_accessibility_controls_are_real_buttons_with_names(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['down', 'reset', 'up'] as $action) {
            $this->assertStringContainsString('data-text-scale="' . $action . '"', $html);
        }

        $this->assertStringContainsString('data-contrast-toggle', $html);
        // A toggle must expose its state, not just look pressed.
        $this->assertStringContainsString('aria-pressed="false"', $html);
    }

    /** The State Emblem of India is restricted by the 2005 Act. */
    public function test_no_national_emblem_is_shipped(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['satyameva', 'सत्यमेव जयते', 'ashoka', 'state-emblem'] as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $html);
        }
    }

    public function test_the_language_menu_uses_real_links_rather_than_hover_only_divs(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // A <div> opened by :hover is unreachable by keyboard and by touch.
        $this->assertStringContainsString('<button type="button" class="language-switcher"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);

        foreach (['en', 'hi', 'mr'] as $locale) {
            $this->assertStringContainsString(route('language.switch-param', $locale), $html);
        }

        // href="#" made document.querySelector throw and carried no destination.
        $this->assertStringNotContainsString('onclick="event.preventDefault(); switchLanguage', $html);
    }

    public function test_every_masthead_string_is_translated(): void
    {
        foreach (['en', 'hi', 'mr'] as $locale) {
            app()->setLocale($locale);

            foreach (['skip_to_main', 'text_size', 'high_contrast', 'jurisdiction', 'last_updated'] as $key) {
                $value = __('messages.' . $key);

                $this->assertNotSame('messages.' . $key, $value, "Missing {$key} for {$locale}.");
            }
        }
    }

    public function test_the_header_offset_is_derived_from_one_token(): void
    {
        $css = file_get_contents(public_path('css/app.css'));

        // Four rules used to hard-code 70px to clear a header whose height is
        // now the sum of the tricolour, the utility strip and the nav row.
        $this->assertStringContainsString('--header-h: calc(', $css);
        $this->assertSame(
            0,
            preg_match('/(margin-top|top):\s*70px/', $css),
            'A rule still hard-codes the old header height.'
        );
    }

    public function test_the_masthead_metrics_are_in_rem_so_the_text_control_scales_them(): void
    {
        $css = file_get_contents(public_path('css/app.css'));

        $this->assertMatchesRegularExpression('/--nav-h:\s*[\d.]+rem/', $css);
        $this->assertMatchesRegularExpression('/--gov-bar-h:\s*[\d.]+rem/', $css);
        $this->assertMatchesRegularExpression('/font-size:\s*calc\(100%\s*\*\s*var\(--text-scale/', $css);
    }

    /**
     * The hero carousel starts on its own and each slide lasts longer than five
     * seconds, which makes a stop mechanism a Level A requirement.
     */
    public function test_the_carousel_can_be_paused(): void
    {
        $this->seedTwoSliders();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="sliderPause"', $html);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $html);
        $this->assertStringContainsString('clearInterval', $html);
    }

    public function test_the_slider_controls_have_accessible_names(): void
    {
        $this->seedTwoSliders();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="prevSlide" aria-label=', $html);
        $this->assertStringContainsString('id="nextSlide" aria-label=', $html);
    }

    private function seedTwoSliders(): void
    {
        foreach ([1, 2] as $order) {
            \App\Models\Slider::create([
                'title' => 'Slide ' . $order,
                'subtitle' => 'Subtitle ' . $order,
                'image' => 'sliders/example-' . $order . '.png',
                'order' => $order,
                'is_active' => true,
            ]);
        }
    }

    public function test_the_homepage_publishes_a_real_property_count_not_an_invented_one(): void
    {
        Cache::flush();

        for ($i = 1; $i <= 4; $i++) {
            PropertyTaxRecord::create([
                'a_no' => $i,
                'customer_no' => 'C-' . $i,
                'property_no' => 'P-' . $i,
                'property_type' => 'Default',
                'customer_name' => 'Owner ' . $i,
                'balance' => 100,
            ]);
        }

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-target="4"', $html);

        // The figures that were fabricated.
        $this->assertStringNotContainsString('data-target="5000"', $html);
        $this->assertStringNotContainsString('data-target="10000"', $html);
    }

    public function test_the_footer_grid_declares_valid_columns(): void
    {
        // Comments are stripped because the fix documents the broken declaration
        // it replaced, and the point here is what the browser parses.
        $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents(public_path('css/app.css')));

        $this->assertMatchesRegularExpression(
            '/\.footer-grid\s*\{[^}]*grid-template-columns:\s*1\.6fr\s+repeat\(3,\s*minmax\(0,\s*1fr\)\)/s',
            $css
        );

        // auto-fit is only reachable through <auto-track-list>, and there every
        // track outside the repeat must be a <fixed-size>. A bare fr is a <flex>,
        // so `2fr repeat(auto-fit, ...)` is invalid and the whole declaration is
        // dropped -- which is what collapsed the footer to one column. Note that
        // repeat(auto-fit, minmax(280px, 1fr)) is perfectly legal, so the check
        // has to be about the fr track sitting outside the repeat.
        preg_match_all('/grid-template-columns:([^;}]+)/', $css, $matches);

        foreach ($matches[1] as $value) {
            if (!str_contains($value, 'auto-fit') && !str_contains($value, 'auto-fill')) {
                continue;
            }

            $outsideRepeat = preg_replace('/repeat\([^)]*(\([^)]*\))?[^)]*\)/', '', $value);

            $this->assertSame(
                0,
                preg_match('/[\d.]+fr/', $outsideRepeat),
                'An fr track sits outside an auto-fit repeat, which invalidates the whole declaration: ' . trim($value)
            );
        }
    }

    public function test_the_footer_names_the_accountable_office_and_a_real_update_date(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('footer-owner', $html);

        // Printing today's date would make the portal claim it was updated today
        // on every request, so the stamp must come from content timestamps.
        if (str_contains($html, 'footer-updated')) {
            $this->assertStringNotContainsString('<time datetime="' . now()->toDateString() . '">', $html);
        }
    }

    public function test_the_footer_carries_the_parent_government_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('india.gov.in', false)
            ->assertSee('maharashtra.gov.in', false);
    }

    /**
     * The first screen a new citizen meets, and previously the worst thing on the
     * site: div options with no keyboard path, no dismissal at all, and a stack
     * measuring roughly 950px against a 667px phone with no scroll, which put the
     * only submit control off-screen behind an opaque overlay.
     */
    public function test_each_language_is_its_own_submit_button(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // One tap per language, so it needs no JavaScript and no select-then-confirm.
        foreach (['en', 'hi', 'mr'] as $locale) {
            $this->assertStringContainsString(
                'name="locale" value="' . $locale . '"',
                $html,
                "The {$locale} option is not a submit button."
            );
        }

        $this->assertStringNotContainsString('onclick="selectLanguage', $html);
        $this->assertStringNotContainsString('id="selectedLocale"', $html);
    }

    public function test_the_language_chooser_is_a_dialog_with_a_way_out(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('role="dialog" aria-modal="true"', $html);
        $this->assertStringContainsString('id="languageModalClose"', $html);
        $this->assertStringContainsString("e.key === 'Escape'", $html);
    }

    public function test_the_language_chooser_can_scroll_on_a_short_viewport(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // A fixed box overflows where the document cannot scroll, so without
        // these the submit buttons are unreachable on a phone in portrait.
        $this->assertMatchesRegularExpression('/\.language-modal-overlay\s*\{[^}]*overflow-y:\s*auto/s', $html);
        $this->assertMatchesRegularExpression('/\.language-modal\s*\{[^}]*max-height:\s*calc\(100vh/s', $html);

        // Three identical flag glyphs distinguished nothing and cost ~209px.
        $this->assertStringNotContainsString('class="flag"', $html);
    }

    public function test_the_chooser_script_does_not_reference_removed_elements(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // getElementById('languageForm').addEventListener would throw a TypeError
        // and take the rest of the inline script down with it.
        $this->assertStringNotContainsString("getElementById('languageForm')", $html);
        $this->assertStringNotContainsString('function switchLanguage', $html);
    }
}
