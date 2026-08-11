<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_emblem_tile_when_no_logo_is_uploaded(): void
    {
        $this->get('/')->assertOk()->assertSee('logo-emblem', false);
    }

    public function test_hero_shows_localized_kicker(): void
    {
        $this->get('/')->assertOk()->assertSee('Official Digital Portal');
    }

    public function test_hero_cta_uses_arrow_chip_button(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('btn-hero', false)
            ->assertSee('btn-arrow', false);
    }

    public function test_quick_action_card_links_to_the_four_core_tasks(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('qa-card', false)
            ->assertSee(route('grievance.create'), false)
            ->assertSee(route('digital-services'), false)
            ->assertSee('Certificates');
    }

    public function test_stats_render_as_a_strip_with_icon_chips(): void
    {
        $this->get('/')->assertOk()->assertSee('stats-strip', false);
    }

    public function test_services_section_has_eyebrow_and_view_all_link(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('section-eyebrow', false)
            ->assertSee(__('messages.view_all'));
    }
}
