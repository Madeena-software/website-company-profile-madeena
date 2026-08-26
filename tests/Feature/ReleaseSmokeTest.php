<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->updateOrCreate(
            ['code' => 'id'],
            [
                'name' => 'Indonesia',
                'native_name' => 'Bahasa Indonesia',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'db' => 'connected',
        ]);
    }

    public function test_up_endpoint(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }

    public function test_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_localized_homepage(): void
    {
        Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Language::create([
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'is_default' => false,
            'is_active' => false,
            'sort_order' => 3,
        ]);

        $this->get('/ja')->assertOk();
        $this->get('/fr')->assertNotFound();
    }

    public function test_article_listing(): void
    {
        $response = $this->get('/artikel');

        $response->assertOk();
    }

    public function test_article_detail(): void
    {
        $author = User::factory()->create(['role' => 'user']);

        $publishedPost = Post::create([
            'user_id' => $author->id,
            'title' => 'Published Scientific Paper',
            'slug' => 'published-scientific-paper',
            'excerpt' => 'An excerpt of scientific research.',
            'content_json' => ['type' => 'doc', 'content' => []],
            'content_language' => 'id',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $unpublishedPost = Post::create([
            'user_id' => $author->id,
            'title' => 'Draft Scientific Paper',
            'slug' => 'draft-scientific-paper',
            'excerpt' => 'Draft excerpt.',
            'content_json' => ['type' => 'doc', 'content' => []],
            'content_language' => 'id',
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->get('/artikel/'.$publishedPost->slug)->assertOk();
        $this->get('/artikel/'.$unpublishedPost->slug)->assertNotFound();
    }

    public function test_product_detail(): void
    {
        $activeProduct = Product::create([
            'name' => 'Madeena DDR System X1',
            'slug' => 'madeena-ddr-system-x1',
            'tagline' => 'High-resolution direct digital radiography system.',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        $inactiveProduct = Product::create([
            'name' => 'Legacy Detector Prototype',
            'slug' => 'legacy-detector-prototype',
            'tagline' => 'Discontinued detector prototype.',
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $this->get('/produk/'.$activeProduct->slug)->assertOk();
        $this->get('/produk/'.$inactiveProduct->slug)->assertNotFound();
    }

    public function test_page_detail(): void
    {
        $publishedPage = Page::create([
            'title' => 'Tentang Perusahaan',
            'slug' => 'tentang-perusahaan',
            'content_json' => [
                [
                    'type' => 'free_text',
                    'data' => ['content' => ['type' => 'doc', 'content' => []]],
                ],
            ],
            'content_language' => 'id',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $unpublishedPage = Page::create([
            'title' => 'Draft Kebijakan Internal',
            'slug' => 'draft-kebijakan-internal',
            'content_json' => [
                [
                    'type' => 'free_text',
                    'data' => ['content' => ['type' => 'doc', 'content' => []]],
                ],
            ],
            'content_language' => 'id',
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->get('/halaman/'.$publishedPage->slug)->assertOk();
        $this->get('/halaman/'.$unpublishedPage->slug)->assertNotFound();
    }

    public function test_event_feedback(): void
    {
        $activeEvent = Event::create([
            'name' => 'Hospital Expo 2026',
            'slug' => 'hospital-expo-2026',
            'description' => 'Medical device exhibition.',
            'is_active' => true,
        ]);

        $inactiveEvent = Event::create([
            'name' => 'Past Radiology Conference',
            'slug' => 'past-radiology-conference',
            'description' => 'Concluded conference.',
            'is_active' => false,
        ]);

        $this->get('/events/'.$activeEvent->slug.'/feedback')->assertOk();
        $this->get('/events/'.$inactiveEvent->slug.'/feedback')->assertNotFound();
    }

    public function test_event_display(): void
    {
        $event = Event::create([
            'name' => 'Screen Display Event',
            'slug' => 'screen-display-event',
            'description' => 'Live exhibition screen display.',
            'is_active' => false,
        ]);

        $this->get('/events/'.$event->slug.'/display')->assertOk();
    }

    public function test_test_support_login_route_boundary_under_testing_env(): void
    {
        $adminEmail = config('auth.filament_admin_email', 'admin@madeena.local');
        $admin = User::factory()->create([
            'email' => $adminEmail,
            'role' => 'admin',
        ]);

        $response = $this->get('/test-support/login');

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }
}
