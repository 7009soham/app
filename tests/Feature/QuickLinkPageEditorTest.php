<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomPage;
use App\Models\QuickLink;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickLinkPageEditorTest extends TestCase
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

    public function test_writing_content_creates_a_page_and_fills_the_url(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.quick-links.store'), [
                'title' => 'Government Schemes',
                'link_type' => 'page',
                'page_content' => '<p>Details of the schemes.</p>',
                'location' => 'footer',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.quick-links.index'));

        $link = QuickLink::firstWhere('title', 'Government Schemes');

        $this->assertNotNull($link);
        $this->assertTrue($link->ownsItsPage());
        $this->assertSame('/page/government-schemes', $link->url);

        // And the page is live, so the link cannot point at a 404.
        $this->get($link->url)->assertOk()->assertSee('Details of the schemes', false);
    }

    public function test_a_plain_url_link_still_works_and_owns_no_page(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.quick-links.store'), [
                'title' => 'Digital Services',
                'link_type' => 'url',
                'url' => '/digital-services',
                'location' => 'footer',
                'is_active' => '1',
            ]);

        $link = QuickLink::firstWhere('title', 'Digital Services');

        $this->assertSame('/digital-services', $link->url);
        $this->assertFalse($link->ownsItsPage());
        $this->assertSame(0, CustomPage::count());
    }

    public function test_content_is_required_when_writing_a_page(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.quick-links.store'), [
                'title' => 'Empty',
                'link_type' => 'page',
                'page_content' => '',
                'location' => 'footer',
            ])
            ->assertSessionHasErrors('page_content');

        $this->assertSame(0, QuickLink::count());
    }

    public function test_a_url_is_required_when_not_writing_a_page(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.quick-links.store'), [
                'title' => 'No target',
                'link_type' => 'url',
                'url' => '',
                'location' => 'footer',
            ])
            ->assertSessionHasErrors('url');
    }

    public function test_editing_updates_the_linked_page_rather_than_creating_another(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post(route('admin.quick-links.store'), [
            'title' => 'Notices',
            'link_type' => 'page',
            'page_content' => '<p>First version.</p>',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        $link = QuickLink::firstWhere('title', 'Notices');

        $this->actingAs($admin, 'admin')->put(route('admin.quick-links.update', $link), [
            'title' => 'Notices',
            'link_type' => 'page',
            'page_content' => '<p>Second version.</p>',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        $this->assertSame(1, CustomPage::count(), 'Editing must not orphan a second page.');
        $this->get($link->fresh()->url)->assertSee('Second version', false);
    }

    public function test_scripts_in_page_content_are_stripped(): void
    {
        $this->actingAs($this->admin(), 'admin')->post(route('admin.quick-links.store'), [
            'title' => 'Notices',
            'link_type' => 'page',
            'page_content' => '<p>Safe</p><script>alert(1)</script>',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        $page = CustomPage::first();

        $this->assertStringNotContainsString('<script', $page->content);
        $this->assertStringContainsString('Safe', $page->content);
    }

    public function test_switching_back_to_a_url_unlinks_but_keeps_the_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post(route('admin.quick-links.store'), [
            'title' => 'Notices',
            'link_type' => 'page',
            'page_content' => '<p>Worth keeping.</p>',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        $link = QuickLink::firstWhere('title', 'Notices');

        $this->actingAs($admin, 'admin')->put(route('admin.quick-links.update', $link), [
            'title' => 'Notices',
            'link_type' => 'url',
            'url' => '/contact',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        $link->refresh();

        $this->assertFalse($link->ownsItsPage());
        $this->assertSame('/contact', $link->url);
        // An accidental toggle must not destroy written content.
        $this->assertSame(1, CustomPage::count());
    }

    public function test_deleting_a_link_keeps_its_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post(route('admin.quick-links.store'), [
            'title' => 'Notices',
            'link_type' => 'page',
            'page_content' => '<p>Still needed.</p>',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        $link = QuickLink::firstWhere('title', 'Notices');

        $this->actingAs($admin, 'admin')->delete(route('admin.quick-links.destroy', $link));

        $this->assertSame(0, QuickLink::count());
        $this->assertSame(1, CustomPage::count());
    }

    public function test_deleting_the_page_leaves_the_link_visible_in_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post(route('admin.quick-links.store'), [
            'title' => 'Notices',
            'link_type' => 'page',
            'page_content' => '<p>Gone soon.</p>',
            'location' => 'footer',
            'is_active' => '1',
        ]);

        CustomPage::first()->delete();

        $link = QuickLink::firstWhere('title', 'Notices');

        // nullOnDelete: the link survives so the broken target is noticeable.
        $this->assertNotNull($link);
        $this->assertNull($link->custom_page_id);
    }
}
