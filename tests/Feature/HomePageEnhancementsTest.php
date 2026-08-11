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
}
