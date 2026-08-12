<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the landing page regressions found reviewing the Civic Indigo
 * redesign. These are CSS-level assertions rather than rendered-layout ones,
 * which is a real limit — but each pins the exact declaration whose absence
 * caused the defect, so a future edit that removes it fails here.
 */
class LandingPageResponsiveTest extends TestCase
{
    use RefreshDatabase;

    private string $css;

    protected function setUp(): void
    {
        parent::setUp();
        $this->css = file_get_contents(public_path('css/app.css'));
    }

    /**
     * A plain `1fr` track has an auto minimum, so the unbreakable counter
     * number ("10,000") set a content floor and the page scrolled sideways on
     * a 320px viewport — a WCAG 2.1 AA 1.4.10 Reflow failure on a site that
     * publishes an AA conformance claim.
     */
    public function test_the_stats_strip_tracks_can_shrink_below_their_content(): void
    {
        $this->assertStringContainsString(
            'grid-template-columns: repeat(4, minmax(0, 1fr))',
            $this->css,
            'Stats tracks must be shrinkable or the counter numbers force horizontal scroll.'
        );

        $this->assertStringContainsString('repeat(2, minmax(0, 1fr))', $this->css);
    }

    public function test_the_stats_strip_stacks_on_the_narrowest_phones(): void
    {
        // The stylesheet has more than one 480px block, so check every one of
        // them rather than assuming the first is the relevant one.
        preg_match_all(
            '/@media \(max-width: 480px\)\s*\{(.*?)\n\}/s',
            $this->css,
            $blocks
        );

        $this->assertNotEmpty($blocks[1], 'The 480px breakpoint block is missing.');

        $stacks = false;
        foreach ($blocks[1] as $block) {
            if (preg_match('/\.stats-strip\s*\{[^}]*grid-template-columns:\s*1fr/s', $block)) {
                $stacks = true;
                break;
            }
        }

        $this->assertTrue(
            $stacks,
            'At 320px two columns of icon chip plus an unbreakable number overflow the viewport.'
        );
    }

    public function test_stat_content_can_shrink_so_long_labels_wrap(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.stat-content\s*\{[^}]*min-width:\s*0/s',
            $this->css
        );
    }

    /**
     * The dots sit in the root stacking context at z-index 10, and the quick
     * actions card is pulled up over the hero — at z-index 2 the white dots
     * painted over the white card and stayed clickable.
     */
    public function test_the_quick_actions_card_paints_above_the_slider_dots(): void
    {
        preg_match('/\.quick-actions\s*\{[^}]*z-index:\s*(\d+)/s', $this->css, $card);
        preg_match('/\.slider-dots\s*\{[^}]*z-index:\s*(\d+)/s', $this->css, $dots);

        $this->assertNotEmpty($card, '.quick-actions must declare a z-index.');
        $this->assertNotEmpty($dots, '.slider-dots must declare a z-index.');
        $this->assertGreaterThan((int) $dots[1], (int) $card[1]);
    }

    /** #d97706 on white is 3.1:1; small text needs 4.5:1. */
    public function test_the_new_badge_uses_an_accessible_amber(): void
    {
        preg_match('/\.qa-item--new::after\s*\{(.*?)\}/s', $this->css, $m);

        $this->assertNotEmpty($m);
        $this->assertStringNotContainsString('var(--color-accent)', $m[1]);
        $this->assertStringContainsString('#b45309', $m[1]);
    }

    public function test_the_new_badge_is_not_rendered_at_a_sub_legible_size(): void
    {
        preg_match('/\.qa-item--new::after\s*\{(.*?)\}/s', $this->css, $m);

        // Strip comments first: the rule documents the old 0.55rem value, and
        // matching on the raw text would fail on the explanation itself.
        $declarations = preg_replace('#/\*.*?\*/#s', '', $m[1]);

        // 0.55rem is 8.8px — Devanagari conjuncts are unreadable at that size.
        $this->assertStringNotContainsString('0.55rem', $declarations);
        $this->assertStringContainsString('var(--font-size-xs)', $declarations);
    }

    /**
     * The kicker sits over an admin-uploaded photo behind a 0.25-alpha scrim,
     * so the backdrop is unknowable and a mid-tone amber could not be relied on.
     */
    public function test_the_hero_kicker_is_legible_over_an_arbitrary_photo(): void
    {
        preg_match('/\.slide-kicker\s*\{(.*?)\}/s', $this->css, $m);

        $this->assertNotEmpty($m);
        $this->assertStringNotContainsString('#f5b04c', $m[1]);
        $this->assertStringContainsString('text-shadow', $m[1]);
    }

    /** Uppercase tracking is a Latin device and fragments Devanagari. */
    public function test_devanagari_locales_drop_latin_letter_spacing(): void
    {
        $this->assertStringContainsString('html[lang="mr"] .slide-kicker', $this->css);
        $this->assertStringContainsString('html[lang="hi"] .qa-item--new::after', $this->css);
    }

    /**
     * The guard must come after the rule it disables: at equal specificity
     * source order decides, and the animation is declared in a later block.
     */
    public function test_the_reduced_motion_guard_wins_over_the_menu_animation(): void
    {
        $animation = strrpos($this->css, 'animation: navItemIn');
        $guard = strrpos($this->css, '.nav-links.active li');

        $this->assertNotFalse($animation);
        $this->assertGreaterThan(
            $animation,
            $guard,
            'A reduced-motion guard declared before the animation is dead code.'
        );
    }

    public function test_the_landing_page_still_renders(): void
    {
        $this->get('/')->assertOk();
    }
}
