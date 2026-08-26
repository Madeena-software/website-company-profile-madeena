<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
    }

    public function test_artikel_index_loads_successfully()
    {
        $this->assertEquals('/artikel', route('artikel.index', [], false));

        $user = \App\Models\User::factory()->create();
        Post::create([
            'title' => 'Test Artikel 1',
            'slug' => 'test-artikel-1',
            'content_json' => [],
            'excerpt' => 'Test Excerpt',
            'user_id' => $user->id,
            'published_at' => now(),
            'is_published' => true,
        ]);

        $response = $this->get(route('artikel.index'));
        $response->assertStatus(200);
        $response->assertSee('Test Artikel 1');
    }

    public function test_product_show_loads_successfully()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'content_json' => [],
            'is_active' => true
        ]);

        $response = $this->get(route('product.show', $product));
        $response->assertStatus(200);
    }

    public function test_post_show_loads_successfully_when_published()
    {
        $user = \App\Models\User::factory()->create();
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content_json' => [],
            'excerpt' => 'Test Excerpt',
            'user_id' => $user->id,
            'published_at' => now(),
            'is_published' => true
        ]);

        $this->assertEquals('/artikel/test-post', route('post.show', $post, false));

        $response = $this->get(route('post.show', $post));
        $response->assertStatus(200);
        $response->assertSee(route('artikel.index'));
        $response->assertSee('Kembali ke Artikel');
    }

    public function test_post_show_returns_404_when_unpublished()
    {
        $user = \App\Models\User::factory()->create();
        $post = Post::create([
            'title' => 'Unpublished Post',
            'slug' => 'unpublished-post',
            'content_json' => [],
            'excerpt' => 'Draft Excerpt',
            'user_id' => $user->id,
            'published_at' => null,
            'is_published' => false,
        ]);

        $response = $this->get(route('post.show', $post));
        $response->assertStatus(404);
    }

    public function test_existing_plain_hero_and_cta_descriptions_render_properly()
    {
        \App\Models\Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'Hero Plain Title',
                            'description' => 'PT Madeena menyediakan layanan radiologi terlengkap.',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'title' => 'CTA Title',
                    'subtitle' => 'Dukung kemandirian alkes buatan Indonesia.',
                    'button_text' => 'Kontak',
                    'button_url' => '#kontak',
                ],
            ],
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('PT Madeena menyediakan layanan radiologi terlengkap.');
        $response->assertSee('Dukung kemandirian alkes buatan Indonesia.');
    }

    public function test_rich_hero_and_cta_descriptions_render_paragraphs_formatting_and_links()
    {
        \App\Models\Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'Rich Hero Title',
                            'description' => '<p>Paragraph 1 with <strong>bold text</strong>.</p><p>Paragraph 2 with <a href="https://madeena.co.id">official link</a>.</p>',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'title' => 'Rich CTA Title',
                    'subtitle' => '<p>First paragraph.</p><ul><li>Benefit 1</li><li>Benefit 2</li></ul>',
                    'button_text' => 'Hubungi Kami',
                    'button_url' => 'https://madeena.co.id/kontak',
                ],
            ],
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Paragraph 1 with <strong>bold text</strong>.', false);
        $response->assertSee('<a href="https://madeena.co.id">official link</a>', false);
        $response->assertSee('First paragraph.');
        $response->assertSee('<li>Benefit 1</li>', false);
        $response->assertSee('<li>Benefit 2</li>', false);
    }

    public function test_rich_hero_and_cta_descriptions_sanitize_script_injection()
    {
        \App\Models\Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'XSS Test Hero',
                            'description' => '<p>Safe Paragraph <script>alert("hero-xss")</script><a href="javascript:alert(1)">Click</a></p>',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'title' => 'XSS Test CTA',
                    'subtitle' => '<p>Safe CTA <script>alert("cta-xss")</script></p>',
                    'button_text' => 'Click',
                    'button_url' => '#',
                ],
            ],
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('<script>alert("hero-xss")</script>', false);
        $response->assertDontSee('<script>alert("cta-xss")</script>', false);
        $response->assertDontSee('javascript:alert(1)', false);
        $response->assertSee('Safe Paragraph');
        $response->assertSee('Safe CTA');
    }

    public function test_hero_template_uses_expanded_copy_width_and_allows_title_wrapping()
    {
        \App\Models\Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'A Very Long Corporate Heading for PT Madeena Karya Indonesia Radiography Innovation',
                            'description' => 'Detailed hero description.',
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // Hero copy should receive 60-65% width (lg:col-span-7 or lg:col-span-8 in a 12-col grid)
        $response->assertSee('lg:col-span-7', false);
        $response->assertSee('lg:col-span-5', false);

        // Hero title must not have whitespace-nowrap that breaks long titles
        $response->assertDontSee('whitespace-nowrap', false);
        $response->assertSee('break-words', false);
    }

    public function test_indonesian_and_english_routes_render_corresponding_content()
    {
        \App\Models\Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'Inovasi Radiografi Indonesia',
                            'description' => 'Solusi teknologi kesehatan karya anak bangsa.',
                        ],
                    ],
                ],
            ],
        ]);

        \App\Models\Setting::setJson('homepage_sections_en', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'Indonesian Radiography Innovation',
                            'description' => 'Healthcare technology solutions made in Indonesia.',
                        ],
                    ],
                ],
            ],
        ]);

        // GET / -> Indonesian
        $idResponse = $this->get('/');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Inovasi Radiografi Indonesia');
        $idResponse->assertDontSee('Healthcare technology solutions made in Indonesia.');
        $idResponse->assertSee('lang="id"', false);

        // GET /en -> English
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Indonesian Radiography Innovation');
        $enResponse->assertDontSee('Solusi teknologi kesehatan karya anak bangsa.');
        $enResponse->assertSee('lang="en"', false);

        // GET /id -> Redirects to /
        $redirectResponse = $this->get('/id');
        $redirectResponse->assertRedirect('/');
    }

    public function test_public_language_switcher_links_and_active_states()
    {
        $idResponse = $this->get('/');
        $idResponse->assertStatus(200);
        $idResponse->assertSee(url('/en'));

        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee(url('/'));
    }

    public function test_admin_and_anonymous_preview_isolation_for_both_languages()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        \App\Models\Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => ['banners' => [['title' => 'Live ID Hero']]],
            ],
        ]);
        \App\Models\Setting::setJson('homepage_sections_draft', [
            [
                'type' => 'hero',
                'data' => ['banners' => [['title' => 'Draft ID Hero']]],
            ],
        ]);
        \App\Models\Setting::setJson('homepage_sections_en', [
            [
                'type' => 'hero',
                'data' => ['banners' => [['title' => 'Live EN Hero']]],
            ],
        ]);
        \App\Models\Setting::setJson('homepage_sections_en_draft', [
            [
                'type' => 'hero',
                'data' => ['banners' => [['title' => 'Draft EN Hero']]],
            ],
        ]);

        // Admin preview ID
        $adminIdPreview = $this->actingAs($admin)->get('/?preview=true');
        $adminIdPreview->assertStatus(200);
        $adminIdPreview->assertSee('Draft ID Hero');
        $adminIdPreview->assertDontSee('Live ID Hero');

        // Admin preview EN
        $adminEnPreview = $this->actingAs($admin)->get('/en?preview=true');
        $adminEnPreview->assertStatus(200);
        $adminEnPreview->assertSee('Draft EN Hero');
        $adminEnPreview->assertDontSee('Live EN Hero');

        // Anonymous preview ID
        auth()->logout();
        $anonIdPreview = $this->get('/?preview=true');
        $anonIdPreview->assertStatus(200);
        $anonIdPreview->assertSee('Live ID Hero');
        $anonIdPreview->assertDontSee('Draft ID Hero');

        // Anonymous preview EN
        $anonEnPreview = $this->get('/en?preview=true');
        $anonEnPreview->assertStatus(200);
        $anonEnPreview->assertSee('Live EN Hero');
        $anonEnPreview->assertDontSee('Draft EN Hero');
    }

    public function test_article_section_filters_posts_by_content_language()
    {
        $user = \App\Models\User::factory()->create();

        // Indonesian Post
        Post::create([
            'title' => 'Berita Teknologi Medis Nasional',
            'slug' => 'berita-medis-nasional',
            'content_json' => [],
            'excerpt' => 'Karya inovasi anak bangsa.',
            'content_language' => 'id',
            'user_id' => $user->id,
            'published_at' => now(),
            'is_published' => true,
        ]);

        // English Post
        Post::create([
            'title' => 'National Medical Technology News',
            'slug' => 'national-med-tech-news',
            'content_json' => [],
            'excerpt' => 'Innovative products from Indonesia.',
            'content_language' => 'en',
            'user_id' => $user->id,
            'published_at' => now(),
            'is_published' => true,
        ]);

        $articleSectionConfig = [
            [
                'type' => 'artikel',
                'data' => [
                    'section_title' => 'Latest Articles',
                    'posts_count' => 5,
                ],
            ],
        ];

        \App\Models\Setting::setJson('homepage_sections', $articleSectionConfig);
        \App\Models\Setting::setJson('homepage_sections_en', $articleSectionConfig);

        // ID homepage must see ID post and NOT EN post
        $idResponse = $this->get('/');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Berita Teknologi Medis Nasional');
        $idResponse->assertDontSee('National Medical Technology News');

        // EN homepage must see EN post and NOT ID post
        $enResponse = $this->get('/en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('National Medical Technology News');
        $enResponse->assertDontSee('Berita Teknologi Medis Nasional');
    }

    public function test_english_homepage_renders_rich_copy_and_localized_static_labels()
    {
        \App\Models\Setting::setJson('homepage_sections_en', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'English Innovation',
                            'description' => '<p>Leading radiography technology in <strong>Indonesia</strong>.</p>',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'data' => [
                    'title' => 'Contact Us Today',
                    'subtitle' => '<p>Get in touch for consultations.</p>',
                    'button_text' => 'Contact',
                    'button_url' => '#contact',
                ],
            ],
        ]);

        $response = $this->get('/en');
        $response->assertStatus(200);
        $response->assertSee('Leading radiography technology in <strong>Indonesia</strong>.', false);
        $response->assertSee('Get in touch for consultations.');
        $response->assertSee('Navigation');
        $response->assertSee('Contact');
        $response->assertSee('All rights reserved.');
    }

    public function test_legacy_public_routes_preserve_indonesian_locale_under_ambient_english()
    {
        // Force ambient application locale to English
        app()->setLocale('en');
        config(['app.locale' => 'en']);

        $user = \App\Models\User::factory()->create();

        $post = Post::create([
            'title' => 'Artikel Berbahasa Indonesia',
            'slug' => 'artikel-berbahasa-indonesia',
            'content_json' => [],
            'excerpt' => 'Ringkasan artikel Indonesia.',
            'content_language' => 'id',
            'user_id' => $user->id,
            'published_at' => now(),
            'is_published' => true,
        ]);

        $product = Product::create([
            'name' => 'Produk Radiografi Indonesia',
            'slug' => 'produk-radiografi-indonesia',
            'content_json' => [],
            'is_active' => true,
        ]);

        $page = \App\Models\Page::create([
            'title' => 'Tentang Perusahaan Kami',
            'slug' => 'tentang-perusahaan-kami',
            'content_json' => [],
            'content_language' => 'id',
            'user_id' => $user->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // 1. GET /artikel
        $artikelResponse = $this->get('/artikel');
        $artikelResponse->assertStatus(200);
        $artikelResponse->assertSee('lang="id"', false);
        $artikelResponse->assertSee('Navigasi');
        $artikelResponse->assertSee('Kontak');
        $artikelResponse->assertSee('Seluruh hak dilindungi.');
        $artikelResponse->assertDontSee('data-testid="language-switcher-desktop"', false);
        $artikelResponse->assertDontSee('data-testid="language-switcher-mobile"', false);

        // 2. GET /artikel/{slug}
        $postResponse = $this->get('/artikel/' . $post->slug);
        $postResponse->assertStatus(200);
        $postResponse->assertSee('lang="id"', false);
        $postResponse->assertSee('Navigasi');
        $postResponse->assertSee('Kontak');
        $postResponse->assertSee('Seluruh hak dilindungi.');
        $postResponse->assertDontSee('data-testid="language-switcher-desktop"', false);
        $postResponse->assertDontSee('data-testid="language-switcher-mobile"', false);

        // 3. GET /produk/{slug}
        $productResponse = $this->get('/produk/' . $product->slug);
        $productResponse->assertStatus(200);
        $productResponse->assertSee('lang="id"', false);
        $productResponse->assertSee('Navigasi');
        $productResponse->assertSee('Kontak');
        $productResponse->assertSee('Seluruh hak dilindungi.');
        $productResponse->assertDontSee('data-testid="language-switcher-desktop"', false);
        $productResponse->assertDontSee('data-testid="language-switcher-mobile"', false);

        // 4. GET /halaman/{slug}
        $pageResponse = $this->get('/halaman/' . $page->slug);
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('lang="id"', false);
        $pageResponse->assertSee('Navigasi');
        $pageResponse->assertSee('Kontak');
        $pageResponse->assertSee('Seluruh hak dilindungi.');
        $pageResponse->assertDontSee('data-testid="language-switcher-desktop"', false);
        $pageResponse->assertDontSee('data-testid="language-switcher-mobile"', false);
    }

    public function test_language_switcher_is_only_visible_on_homepage_routes()
    {
        // 1. GET / -> Has switcher
        $homeId = $this->get('/');
        $homeId->assertStatus(200);
        $homeId->assertSee('data-testid="language-switcher-desktop"', false);
        $homeId->assertSee('data-testid="language-switcher-mobile"', false);

        // 2. GET /en -> Has switcher
        $homeEn = $this->get('/en');
        $homeEn->assertStatus(200);
        $homeEn->assertSee('data-testid="language-switcher-desktop"', false);
        $homeEn->assertSee('data-testid="language-switcher-mobile"', false);

        // 3. GET /artikel -> Switcher hidden
        $artikel = $this->get('/artikel');
        $artikel->assertStatus(200);
        $artikel->assertDontSee('data-testid="language-switcher-desktop"', false);
        $artikel->assertDontSee('data-testid="language-switcher-mobile"', false);
    }

    public function test_generic_localized_homepage_route_and_404_for_unsupported_language()
    {
        \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        Setting::setJson('homepage_sections_ja', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => '日本の医療機器',
                            'description' => '<p>インドネシア製</p>',
                        ],
                    ],
                ],
            ],
        ]);

        // GET /ja -> 200
        $response = $this->get('/ja');
        $response->assertStatus(200);
        $response->assertSee('lang="ja"', false);
        $response->assertSee('日本の医療機器');

        // GET /zz (unregistered) -> 404
        $notFound = $this->get('/zz');
        $notFound->assertStatus(404);
    }

    public function test_default_language_redirect_and_switching_default()
    {
        $ja = \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        Setting::setJson('homepage_sections', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [['title' => 'ID Hero Title', 'description' => 'ID Desc']],
                ],
            ],
        ]);

        Setting::setJson('homepage_sections_ja', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [['title' => 'JA Hero Title', 'description' => 'JA Desc']],
                ],
            ],
        ]);

        // When ID is default, GET /id redirects to /
        $this->get('/id')->assertRedirect('/');

        // Now set JA as default
        $ja->setAsDefault();

        // GET / -> JA homepage
        $home = $this->get('/');
        $home->assertStatus(200);
        $home->assertSee('lang="ja"', false);
        $home->assertSee('JA Hero Title');

        // GET /ja -> redirects to /
        $this->get('/ja')->assertRedirect('/');

        // GET /id -> ID homepage
        $idHome = $this->get('/id');
        $idHome->assertStatus(200);
        $idHome->assertSee('lang="id"', false);
        $idHome->assertSee('ID Hero Title');
    }

    public function test_dynamic_language_switcher_lists_all_active_languages_and_hides_inactive()
    {
        \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        \App\Models\Language::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'is_active' => false,
            'is_default' => false,
            'sort_order' => 4,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Active codes present in switcher
        $response->assertSee('data-testid="language-switcher-desktop"', false);
        $response->assertSee('ID');
        $response->assertSee('EN');
        $response->assertSee('JA');
        $response->assertSee(url('/ja'));

        // Inactive code absent
        $response->assertDontSee('data-testid="lang-link-de"', false);
        $response->assertDontSee('Deutsch');
        $response->assertDontSee(url('/de'));
    }

    public function test_data_driven_ui_labels_and_default_fallback_for_third_language()
    {
        \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'ui_labels' => [
                'navigation' => 'ナビゲーション',
                'contact' => 'お問い合わせ',
                // 'all_rights_reserved' is intentionally omitted to test fallback to default
            ],
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        Setting::setJson('homepage_sections_ja', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [['title' => 'JA Title', 'description' => 'JA Desc']],
                ],
            ],
        ]);

        $response = $this->get('/ja');
        $response->assertStatus(200);
        $response->assertSee('ナビゲーション');
        $response->assertSee('お問い合わせ');
        // Falls back to default Indonesian string
        $response->assertSee('Seluruh hak dilindungi.');
    }

    public function test_third_language_preview_confidentiality()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        Setting::setJson('homepage_sections_ja', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [['title' => 'Published JA Title', 'description' => 'Published JA Desc']],
                ],
            ],
        ]);

        Setting::setJson('homepage_sections_ja_draft', [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [['title' => 'Draft JA Title', 'description' => 'Draft JA Desc']],
                ],
            ],
        ]);

        // 1. Admin with preview sees JA draft
        $adminPreview = $this->actingAs($admin)->get('/ja?preview=true');
        $adminPreview->assertStatus(200);
        $adminPreview->assertSee('Draft JA Title');
        $adminPreview->assertDontSee('Published JA Title');

        // Logout
        auth()->logout();

        // 2. Anonymous with preview parameter MUST NOT see draft
        $anonPreview = $this->get('/ja?preview=true');
        $anonPreview->assertStatus(200);
        $anonPreview->assertSee('Published JA Title');
        $anonPreview->assertDontSee('Draft JA Title');
    }

    public function test_non_id_default_language_switches_homepage_content_language_without_affecting_explicit_id_routes()
    {
        $user = \App\Models\User::factory()->create();

        $postJa = Post::create([
            'title' => '日本の医療機器最新記事',
            'slug' => 'nihon-no-iryou-kiki',
            'content' => 'Japanese Content',
            'user_id' => $user->id,
            'is_published' => true,
            'published_at' => now(),
            'content_language' => 'ja',
        ]);

        $postId = Post::create([
            'title' => 'Artikel Bahasa Indonesia',
            'slug' => 'artikel-bahasa-indonesia',
            'content' => 'Indonesian Content',
            'user_id' => $user->id,
            'is_published' => true,
            'published_at' => now(),
            'content_language' => 'id',
        ]);

        $ja = \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        // Set Japanese as the default language
        $ja->setAsDefault();

        $articleSection = [
            [
                'type' => 'artikel',
                'data' => [
                    'section_title' => 'Articles Section',
                    'posts_count' => 10,
                ],
            ],
        ];

        Setting::setJson('homepage_sections', $articleSection);
        Setting::setJson('homepage_sections_ja', $articleSection);

        // 1. GET / -> JA default homepage
        $jaHome = $this->get('/');
        $jaHome->assertStatus(200);
        $jaHome->assertSee('lang="ja"', false);
        $jaHome->assertSee('日本の医療機器最新記事');
        $jaHome->assertDontSee('Artikel Bahasa Indonesia');

        // 2. GET /id -> ID homepage
        $idHome = $this->get('/id');
        $idHome->assertStatus(200);
        $idHome->assertSee('lang="id"', false);
        $idHome->assertSee('Artikel Bahasa Indonesia');
        $idHome->assertDontSee('日本の医療機器最新記事');
    }

    public function test_third_language_ui_labels_with_placeholders_render_without_lang_files()
    {
        $user = \App\Models\User::factory()->create();

        Post::create([
            'title' => '最新のイノベーション記事',
            'slug' => 'saishin-innovation',
            'content' => 'JA Body',
            'user_id' => $user->id,
            'is_published' => true,
            'published_at' => now(),
            'content_language' => 'ja',
        ]);

        \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'ui_labels' => [
                'read' => '読む',
                'view_all' => 'すべての:titleを見る',
                'articles' => '記事',
                'manage_website_in_admin' => '管理画面でウェブサイトを管理',
            ],
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        // 1. Artikel section with custom section title
        Setting::setJson('homepage_sections_ja', [
            [
                'type' => 'artikel',
                'data' => [
                    'section_title' => '最新ニュース',
                    'posts_count' => 3,
                ],
            ],
        ]);

        $response = $this->get('/ja');
        $response->assertStatus(200);
        $response->assertSee('読む');
        $response->assertSee('すべての最新ニュースを見る');
        $response->assertDontSee('Baca');
        $response->assertDontSee('Lihat Semua');

        // 2. Empty homepage fallback test
        Setting::setJson('homepage_sections_ja', []);

        $emptyResponse = $this->get('/ja');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('管理画面でウェブサイトを管理');
        $emptyResponse->assertDontSee('Kelola Website di Admin');
    }

    public function test_scalable_language_switcher_with_small_vs_five_plus_languages_and_preview_query_retention()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        // 1. Small language set (ID + EN): inline layout
        $smallResponse = $this->get('/');
        $smallResponse->assertStatus(200);
        $smallResponse->assertSee('data-layout="inline"', false);
        $smallResponse->assertDontSee('data-layout="dropdown"', false);

        // 2. Add 3 more active languages and 1 inactive language (5 active total: id, en, ja, ar, fr; 1 inactive: de)
        \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        \App\Models\Language::create([
            'code' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 4,
        ]);

        \App\Models\Language::create([
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 5,
        ]);

        \App\Models\Language::create([
            'code' => 'de',
            'name' => 'German',
            'native_name' => 'Deutsch',
            'is_active' => false,
            'is_default' => false,
            'sort_order' => 6,
        ]);

        // 3. Large language set: scalable dropdown layout rendered
        $largeResponse = $this->get('/');
        $largeResponse->assertStatus(200);
        $largeResponse->assertSee('data-layout="dropdown"', false);
        $largeResponse->assertSee('data-testid="language-dropdown-toggle"', false);
        $largeResponse->assertSee('data-testid="mobile-language-dropdown-toggle"', false);
        $largeResponse->assertSee('日本語');
        $largeResponse->assertSee('العربية');
        $largeResponse->assertSee('Français');
        // Inactive language is absent
        $largeResponse->assertDontSee('Deutsch');
        $largeResponse->assertDontSee('/de');

        // 4. Admin preview URL preservation
        $previewResponse = $this->actingAs($admin)->get('/?preview=true');
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee(url('/en?preview=true'));
        $previewResponse->assertSee(url('/ja?preview=true'));
        $previewResponse->assertSee(url('/ar?preview=true'));
        $previewResponse->assertSee(url('/fr?preview=true'));
    }

    public function test_inactive_language_preview_security_and_activation_flow()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $user = \App\Models\User::factory()->create(['role' => 'user']);

        $ja = \App\Models\Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => false,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        $jaDraft = [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'JA Draft Secret Preview',
                        ],
                    ],
                ],
            ],
        ];

        $jaPublished = [
            [
                'type' => 'hero',
                'data' => [
                    'banners' => [
                        [
                            'title' => 'JA Published Live Hero',
                        ],
                    ],
                ],
            ],
        ];

        Setting::setJson('homepage_sections_ja_draft', $jaDraft);
        Setting::setJson('homepage_sections_ja', $jaPublished);

        // 1. Guest normal route -> 404
        $this->get('/ja')->assertNotFound();

        // 2. Guest preview attempt -> 404
        $this->get('/ja?preview=true')->assertNotFound();

        // 3. Non-admin preview attempt -> 404
        $this->actingAs($user)->get('/ja?preview=true')->assertNotFound();

        // 4. Admin preview -> 200 OK & sees draft content
        $adminPreview = $this->actingAs($admin)->get('/ja?preview=true');
        $adminPreview->assertStatus(200);
        $adminPreview->assertSee('JA Draft Secret Preview');
        $adminPreview->assertDontSee('JA Published Live Hero');

        // 5. Public switcher excludes inactive JA
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertDontSee('data-testid="lang-link-ja"', false);
        $homeResponse->assertDontSee('/ja');

        // 6. Activate JA -> public GET /ja becomes available with published content
        $ja->update(['is_active' => true]);

        $publicJa = $this->get('/ja');
        $publicJa->assertStatus(200);
        $publicJa->assertSee('JA Published Live Hero');
        $publicJa->assertDontSee('JA Draft Secret Preview');
    }
}
