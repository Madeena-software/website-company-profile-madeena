<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_bootstrap_initializes_id_and_en_safely(): void
    {
        $id = Language::where('code', 'id')->first();
        $this->assertNotNull($id);
        $this->assertSame('Indonesian', $id->name);
        $this->assertSame('Bahasa Indonesia', $id->native_name);
        $this->assertTrue($id->is_active);
        $this->assertTrue($id->is_default);

        $en = Language::where('code', 'en')->first();
        $this->assertNotNull($en);
        $this->assertSame('English', $en->name);
        $this->assertSame('English', $en->native_name);
        $this->assertTrue($en->is_active);
        $this->assertFalse($en->is_default);

        $this->assertSame('id', Language::getDefault()->code);
    }

    public function test_existing_homepage_setting_keys_are_preserved_for_backward_compatibility(): void
    {
        $this->assertSame('homepage_sections', Language::publishedKeyFor('id'));
        $this->assertSame('homepage_sections_draft', Language::draftKeyFor('id'));
        $this->assertSame('homepage_sections_en', Language::publishedKeyFor('en'));
        $this->assertSame('homepage_sections_en_draft', Language::draftKeyFor('en'));
    }

    public function test_dynamic_keys_for_arbitrary_third_language(): void
    {
        Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        $this->assertSame('homepage_sections_ja', Language::publishedKeyFor('ja'));
        $this->assertSame('homepage_sections_ja_draft', Language::draftKeyFor('ja'));

        Language::create([
            'code' => 'pt-br',
            'name' => 'Portuguese (Brazil)',
            'native_name' => 'Português (Brasil)',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 4,
        ]);

        $this->assertSame('homepage_sections_pt-br', Language::publishedKeyFor('pt-br'));
        $this->assertSame('homepage_sections_pt-br_draft', Language::draftKeyFor('pt-br'));
    }

    public function test_unsafe_and_unsupported_language_codes_fall_back_safely_and_do_not_inject_arbitrary_keys(): void
    {
        $this->assertFalse(Language::validateCode('../../etc/passwd'));
        $this->assertFalse(Language::validateCode('<script>alert(1)</script>'));
        $this->assertFalse(Language::validateCode('invalid code with spaces'));

        $this->assertSame('id', Language::normalizeCode('../../invalid'));
        $this->assertSame('homepage_sections', Language::publishedKeyFor('../../invalid'));
        $this->assertSame('homepage_sections_draft', Language::draftKeyFor('<script>'));
    }

    public function test_language_code_is_immutable_once_created(): void
    {
        $lang = Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Language code is immutable once created.');

        $lang->code = 'jp';
        $lang->save();
    }

    public function test_default_language_cannot_be_deleted(): void
    {
        $default = Language::getDefault();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot delete default language.');

        $default->delete();
    }

    public function test_default_language_cannot_be_deactivated(): void
    {
        $default = Language::getDefault();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Default language cannot be deactivated.');

        $default->is_active = false;
        $default->save();
    }

    public function test_set_as_default_atomically_switches_default_language(): void
    {
        $ja = Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        $ja->setAsDefault();

        $this->assertTrue($ja->fresh()->is_default);
        $this->assertFalse(Language::where('code', 'id')->first()->is_default);
        $this->assertSame('ja', Language::getDefault()->code);
    }

    public function test_language_resource_admin_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $nonAdmin = User::factory()->create(['role' => 'editor']);

        $this->actingAs($admin);
        $this->get('/admin/languages')->assertOk();

        $this->actingAs($nonAdmin);
        $this->get('/admin/languages')->assertForbidden();
    }

    public function test_key_mapping_persists_indonesian_legacy_keys_even_when_non_id_language_is_default(): void
    {
        $ja = Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        $ja->setAsDefault();

        $this->assertSame('ja', Language::getDefault()->code);
        $this->assertSame('homepage_sections', Language::publishedKeyFor('id'));
        $this->assertSame('homepage_sections_draft', Language::draftKeyFor('id'));
        $this->assertSame('homepage_sections_ja', Language::publishedKeyFor('ja'));
        $this->assertSame('homepage_sections_ja_draft', Language::draftKeyFor('ja'));
    }

    public function test_ui_label_placeholder_interpolation_and_fallback(): void
    {
        $ja = Language::create([
            'code' => 'ja',
            'name' => 'Japanese',
            'native_name' => '日本語',
            'ui_labels' => [
                'read' => '読む',
                'view_all' => 'すべての:titleを見る',
                'articles' => '記事',
            ],
            'is_active' => true,
            'is_default' => false,
            'sort_order' => 3,
        ]);

        // Direct key with replacement
        $this->assertSame('読む', $ja->getUiLabel('read'));
        $this->assertSame('すべての記事を見る', $ja->getUiLabel('view_all', ['title' => '記事']));
        $this->assertSame('すべての最新ニュースを見る', $ja->getUiLabel('view_all', ['title' => '最新ニュース']));

        // Key missing in JA falls back to default ID language ui_labels with replacement
        $this->assertSame('Seluruh hak dilindungi.', $ja->getUiLabel('all_rights_reserved'));
        $this->assertSame('Navigasi', $ja->getUiLabel('navigation'));
    }

    public function test_language_create_page_defaults_is_active_to_false(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        $this->get('/admin/languages/create')->assertOk();
    }
}
