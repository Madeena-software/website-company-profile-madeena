<?php

namespace Tests\Feature;

use App\Filament\Resources\PageResource;
use App\Models\Language;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PagePublicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
    }

    /**
     * A. New Page defaults to Draft / Unpublished
     */
    public function test_new_page_defaults_to_draft_and_null_published_at()
    {
        $page = Page::create([
            'title' => 'Dokumen Baru',
            'slug' => 'dokumen-baru',
            'content_json' => [
                [
                    'type' => 'free_text',
                    'data' => [
                        'content' => [
                            'type' => 'doc',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [['type' => 'text', 'text' => 'Konten rahasia draft.']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFalse($page->is_published);
        $this->assertNull($page->published_at);

        $fresh = Page::find($page->id);
        $this->assertFalse($fresh->is_published);
        $this->assertNull($fresh->published_at);
    }

    /**
     * B. Existing data migration compatibility (backfill logic test)
     */
    public function test_migration_backfills_pre_existing_pages_as_published()
    {
        // Insert a raw record simulating a pre-migration record where publication columns are updated via migration logic
        $id = DB::table('pages')->insertGetId([
            'title' => 'Legacy Public Page',
            'slug' => 'legacy-public-page',
            'content_json' => json_encode([]),
            'content_language' => 'id',
            'enable_auto_numbering' => 1,
            'show_in_header' => 0,
            'show_in_footer' => 0,
            'created_at' => '2025-01-01 10:00:00',
            'updated_at' => '2025-01-01 10:00:00',
            'is_published' => false,
            'published_at' => null,
        ]);

        // Run the backfill update query as defined in the migration
        DB::table('pages')->where('id', $id)->update([
            'is_published' => true,
            'published_at' => DB::raw('COALESCE(created_at, updated_at, CURRENT_TIMESTAMP)'),
        ]);

        $page = Page::find($id);
        $this->assertTrue($page->is_published);
        $this->assertNotNull($page->published_at);
        $this->assertEquals('2025-01-01 10:00:00', $page->published_at->format('Y-m-d H:i:s'));
    }

    /**
     * C. Unpublished public route returns 404 for visitors
     */
    public function test_unpublished_page_returns_404_for_public_visitor()
    {
        $page = Page::create([
            'title' => 'Halaman Belum Terbit',
            'slug' => 'halaman-belum-terbit',
            'content_json' => [],
            'is_published' => false,
        ]);

        $response = $this->get('/halaman/' . $page->slug);
        $response->assertStatus(404);
    }

    /**
     * D. Published public route returns 200
     */
    public function test_published_page_returns_200_for_public_visitor()
    {
        $page = Page::create([
            'title' => 'Halaman Sudah Terbit',
            'slug' => 'halaman-sudah-terbit',
            'content_json' => [
                [
                    'type' => 'free_text',
                    'data' => [
                        'content' => [
                            'type' => 'doc',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [['type' => 'text', 'text' => 'Selamat datang di halaman publik.']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/halaman/' . $page->slug);
        $response->assertStatus(200);
        $response->assertSee('Halaman Sudah Terbit');
        $response->assertSee('Selamat datang di halaman publik.');
    }

    /**
     * E. Admin draft preview returns 200 with draft content and preview mode notice
     */
    public function test_authenticated_admin_can_preview_unpublished_page()
    {
        $page = Page::create([
            'title' => 'Draft Rahasia Admin',
            'slug' => 'draft-rahasia-admin',
            'content_json' => [
                [
                    'type' => 'free_text',
                    'data' => [
                        'content' => [
                            'type' => 'doc',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [['type' => 'text', 'text' => 'Teks pratinjau khusus admin.']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'is_published' => false,
        ]);

        // Admin without preview=true query param gets 404 on draft page
        $this->actingAs($this->admin)->get('/halaman/' . $page->slug)->assertStatus(404);

        // Admin with preview=true query param gets 200 and sees draft content
        $response = $this->actingAs($this->admin)->get('/halaman/' . $page->slug . '?preview=true');
        $response->assertStatus(200);
        $response->assertSee('Draft Rahasia Admin');
        $response->assertSee('Teks pratinjau khusus admin.');
        $response->assertSee('Mode Pratinjau (Draft)');
    }

    /**
     * F. Guest preview protection (guest requesting preview=true gets 404)
     */
    public function test_guest_cannot_preview_unpublished_page()
    {
        $page = Page::create([
            'title' => 'Draft Rahasia',
            'slug' => 'draft-rahasia',
            'content_json' => [],
            'is_published' => false,
        ]);

        $response = $this->get('/halaman/' . $page->slug . '?preview=true');
        $response->assertStatus(404);
    }

    /**
     * G. Non-admin preview protection (regular authenticated user requesting preview=true gets 404)
     */
    public function test_non_admin_user_cannot_preview_unpublished_page()
    {
        $page = Page::create([
            'title' => 'Draft Khusus Manajemen',
            'slug' => 'draft-khusus-manajemen',
            'content_json' => [],
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->regularUser)->get('/halaman/' . $page->slug . '?preview=true');
        $response->assertStatus(404);
    }

    /**
     * H. Publish action updates publication fields
     */
    public function test_publish_action_sets_is_published_and_published_at()
    {
        $page = Page::create([
            'title' => 'Draft to Publish',
            'slug' => 'draft-to-publish',
            'content_json' => [],
            'is_published' => false,
            'published_at' => null,
        ]);

        $this->assertFalse($page->is_published);
        $this->assertNull($page->published_at);

        $page->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $fresh = Page::find($page->id);
        $this->assertTrue($fresh->is_published);
        $this->assertNotNull($fresh->published_at);

        $response = $this->get('/halaman/' . $fresh->slug);
        $response->assertStatus(200);
    }

    /**
     * I. Unpublish action revokes publication and sets public route to 404
     */
    public function test_unpublish_action_revokes_public_access()
    {
        $page = Page::create([
            'title' => 'Live to Unpublish',
            'slug' => 'live-to-unpublish',
            'content_json' => [],
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get('/halaman/' . $page->slug)->assertStatus(200);

        $page->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        $fresh = Page::find($page->id);
        $this->assertFalse($fresh->is_published);
        $this->assertNull($fresh->published_at);

        $this->get('/halaman/' . $fresh->slug)->assertStatus(404);
    }

    /**
     * J. Autosave / editing draft content does not implicitly publish
     */
    public function test_autosave_or_editing_content_does_not_implicitly_publish_draft_page()
    {
        $page = Page::create([
            'title' => 'Draft In Progress',
            'slug' => 'draft-in-progress',
            'content_json' => [],
            'is_published' => false,
            'published_at' => null,
        ]);

        // Simulating autosave content updates
        $page->update([
            'content_json' => [
                [
                    'type' => 'free_text',
                    'data' => ['content' => ['type' => 'doc', 'content' => []]],
                ],
            ],
        ]);

        $fresh = Page::find($page->id);
        $this->assertFalse($fresh->is_published);
        $this->assertNull($fresh->published_at);
    }

    /**
     * K. Public homepage reference: does not leak unpublished Page content
     */
    public function test_public_homepage_does_not_leak_unpublished_page_reference()
    {
        $draftPage = Page::create([
            'title' => 'Rahasia Internal Perusahaan',
            'summary' => 'Ringkasan rahasia yang tidak boleh dilihat publik.',
            'slug' => 'rahasia-internal-perusahaan',
            'content_json' => [],
            'is_published' => false,
        ]);

        Setting::setJson('homepage_sections', [
            [
                'type' => 'about',
                'data' => [
                    'section_id' => 'tentang',
                    'page_id' => $draftPage->id,
                ],
            ],
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        // Must NOT leak draft page title or summary
        $response->assertDontSee('Rahasia Internal Perusahaan');
        $response->assertDontSee('Ringkasan rahasia yang tidak boleh dilihat publik.');
        $response->assertSee('Halaman belum diatur.');
    }

    /**
     * L. Admin homepage preview: can see referenced unpublished Page
     */
    public function test_admin_homepage_preview_can_render_referenced_unpublished_page()
    {
        $draftPage = Page::create([
            'title' => 'Pratinjau Tentang Kami',
            'summary' => 'Ringkasan pratinjau tentang perusahaan kami.',
            'slug' => 'pratinjau-tentang-kami',
            'content_json' => [],
            'is_published' => false,
        ]);

        Setting::setJson('homepage_sections', [
            [
                'type' => 'about',
                'data' => [
                    'section_id' => 'tentang',
                    'page_id' => $draftPage->id,
                ],
            ],
        ]);

        // Guest requesting homepage preview cannot see draft page reference
        $guestResponse = $this->get('/?preview=true');
        $guestResponse->assertStatus(200);
        $guestResponse->assertDontSee('Pratinjau Tentang Kami');

        // Admin preview sees the draft page reference
        $adminResponse = $this->actingAs($this->admin)->get('/?preview=true');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Pratinjau Tentang Kami');
        $adminResponse->assertSee('Ringkasan pratinjau tentang perusahaan kami.');
    }

    /**
     * M. Published page reference continues to render on homepage for all visitors
     */
    public function test_public_homepage_renders_published_page_reference()
    {
        $publishedPage = Page::create([
            'title' => 'Profil PT Madeena Karya',
            'summary' => 'Produsen Digital Radiography pertama di Indonesia.',
            'slug' => 'profil-pt-madeena-karya',
            'content_json' => [],
            'is_published' => true,
            'published_at' => now(),
        ]);

        Setting::setJson('homepage_sections', [
            [
                'type' => 'about',
                'data' => [
                    'section_id' => 'tentang',
                    'page_id' => $publishedPage->id,
                ],
            ],
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Profil PT Madeena Karya');
        $response->assertSee('Produsen Digital Radiography pertama di Indonesia.');
    }
}
