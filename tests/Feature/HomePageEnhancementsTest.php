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
}
