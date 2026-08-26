<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_render_page_index()
    {
        $this->actingAs($this->admin)->get(PageResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_page_create()
    {
        $this->actingAs($this->admin)->get(PageResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_render_page_edit()
    {
        $page = Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content_json' => [],
            'is_published' => false,
        ]);

        $this->actingAs($this->admin)->get(PageResource::getUrl('edit', ['record' => $page]))->assertSuccessful();
    }

    public function test_non_admin_cannot_access_page_resource()
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $this->actingAs($regularUser)->get(PageResource::getUrl('index'))->assertForbidden();
        $this->actingAs($regularUser)->get(PageResource::getUrl('create'))->assertForbidden();
    }
}
