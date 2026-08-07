<?php

namespace Tests\Feature;

use App\Helpers\RichText;
use App\Models\Admin;
use App\Models\CustomPage;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'type' => 'superadmin',
            'is_active' => true,
        ]);

        return Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function page(array $attributes = []): CustomPage
    {
        return CustomPage::create(array_merge([
            'title' => 'Government Schemes',
            'slug' => 'government-schemes',
            'content' => '<p>Details of available schemes.</p>',
            'is_published' => true,
        ], $attributes));
    }

    public function test_a_published_page_is_publicly_reachable(): void
    {
        $this->page();

        $this->get('/page/government-schemes')
            ->assertOk()
            ->assertSee('Government Schemes')
            ->assertSee('Details of available schemes', false);
    }

    public function test_an_unpublished_page_returns_404(): void
    {
        $this->page(['is_published' => false]);

        // Drafts must not be readable by guessing the URL.
        $this->get('/page/government-schemes')->assertNotFound();
    }

    public function test_an_unknown_slug_returns_404(): void
    {
        $this->get('/page/does-not-exist')->assertNotFound();
    }

    public function test_scripts_are_stripped_from_content_on_save(): void
    {
        $page = $this->page([
            'content' => '<p>Safe text</p><script>alert(document.cookie)</script>',
        ]);

        $this->assertStringNotContainsString('<script', $page->content);
        $this->assertStringNotContainsString('alert(', $page->content);
        $this->assertStringContainsString('Safe text', $page->content);
    }

    public function test_inline_event_handlers_and_javascript_urls_are_stripped(): void
    {
        $page = $this->page([
            'content' => '<p onclick="steal()">Hi</p><a href="javascript:alert(1)">Click</a>',
        ]);

        $this->assertStringNotContainsString('onclick', $page->content);
        $this->assertStringNotContainsString('javascript:', $page->content);
    }

    public function test_stripping_survives_a_rendered_page(): void
    {
        $this->page(['content' => '<p>Hello</p><script>alert(1)</script>']);

        $this->get('/page/government-schemes')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_formatting_and_links_are_preserved(): void
    {
        $page = $this->page([
            'content' => '<h2>Heading</h2><p><strong>Bold</strong> and <a href="/contact">a link</a></p><ul><li>Item</li></ul>',
        ]);

        foreach (['<h2>', '<strong>', '<a href="/contact">', '<ul>', '<li>'] as $fragment) {
            $this->assertStringContainsString($fragment, $page->content);
        }
    }

    public function test_slugs_are_made_unique_rather_than_colliding(): void
    {
        $this->page();

        $this->assertSame('government-schemes-2', CustomPage::uniqueSlug('Government Schemes'));

        // Editing the same page keeps its own slug.
        $existing = CustomPage::first();
        $this->assertSame('government-schemes', CustomPage::uniqueSlug('Government Schemes', $existing->id));
    }

    public function test_admin_can_create_a_page_and_the_slug_is_derived_from_the_title(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.custom-pages.store'), [
                'title' => 'Committee Members',
                'slug' => '',
                'content' => '<p>List of members.</p>',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.custom-pages.index'));

        $page = CustomPage::firstWhere('title', 'Committee Members');

        $this->assertNotNull($page);
        $this->assertSame('committee-members', $page->slug);
        $this->assertTrue($page->is_published);
    }

    public function test_unchecking_published_sends_a_page_back_to_draft(): void
    {
        $page = $this->page();

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.custom-pages.update', $page), [
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                // is_published intentionally absent, as an unchecked box posts nothing.
            ]);

        $this->assertFalse($page->fresh()->is_published);
        $this->get('/page/government-schemes')->assertNotFound();
    }

    public function test_guests_cannot_reach_the_page_admin(): void
    {
        $this->get(route('admin.custom-pages.index'))->assertRedirect();
        $this->post(route('admin.custom-pages.store'), [])->assertRedirect();
    }

    public function test_excerpt_falls_back_to_the_content_when_no_summary_is_set(): void
    {
        $page = $this->page(['content' => '<p>' . str_repeat('word ', 60) . '</p>']);

        $this->assertStringEndsWith('…', $page->excerpt);
        $this->assertLessThanOrEqual(160, mb_strlen($page->excerpt));
    }

    public function test_rich_text_helper_handles_empty_input(): void
    {
        $this->assertSame('', RichText::sanitize(null));
        $this->assertSame('', RichText::sanitize('   '));
    }
}
