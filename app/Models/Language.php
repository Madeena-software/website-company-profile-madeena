<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'ui_labels',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'ui_labels' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function validateCode(?string $code): bool
    {
        if (empty($code)) {
            return false;
        }

        return (bool) preg_match('/^[a-z]{2,3}(-[a-z0-9]{2,4})?$/', strtolower(trim($code)));
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtolower(trim((string) $value));
    }

    protected static function booted(): void
    {
        static::updating(function (Language $language) {
            if ($language->isDirty('code')) {
                throw new \InvalidArgumentException("Language code is immutable once created.");
            }

            // Prevent deactivating default language
            if ($language->is_default && $language->isDirty('is_active') && ! $language->is_active) {
                throw new \InvalidArgumentException("Default language cannot be deactivated.");
            }
        });

        static::deleting(function (Language $language) {
            if ($language->is_default) {
                throw new \InvalidArgumentException("Cannot delete default language.");
            }
        });
    }

    public function setAsDefault(): void
    {
        DB::transaction(function () {
            static::query()->where('id', '!=', $this->id)->update(['is_default' => false]);
            $this->is_default = true;
            $this->is_active = true;
            $this->save();
        });
    }

    public static function ensureDefaultLanguagesExist(): void
    {
        try {
            $now = now();
            if (! static::where('code', 'id')->exists()) {
                static::insert([
                    'code' => 'id',
                    'name' => 'Indonesian',
                    'native_name' => 'Bahasa Indonesia',
                    'ui_labels' => json_encode([
                        'navigation' => 'Navigasi',
                        'contact' => 'Kontak',
                        'all_rights_reserved' => 'Seluruh hak dilindungi.',
                        'preview_mode_notice' => 'Anda sedang melihat Mode Pratinjau (Draft).',
                        'back' => 'Kembali',
                        'language' => 'Bahasa',
                        'read' => 'Baca',
                        'view_all' => 'Lihat Semua :title',
                        'articles' => 'Artikel',
                        'manage_website_in_admin' => 'Kelola Website di Admin',
                    ]),
                    'is_active' => true,
                    'is_default' => true,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (! static::where('code', 'en')->exists()) {
                static::insert([
                    'code' => 'en',
                    'name' => 'English',
                    'native_name' => 'English',
                    'ui_labels' => json_encode([
                        'navigation' => 'Navigation',
                        'contact' => 'Contact',
                        'all_rights_reserved' => 'All rights reserved.',
                        'preview_mode_notice' => 'You are viewing Preview Mode (Draft).',
                        'back' => 'Back',
                        'language' => 'Language',
                        'read' => 'Read',
                        'view_all' => 'View All :title',
                        'articles' => 'Articles',
                        'manage_website_in_admin' => 'Manage Website in Admin',
                    ]),
                    'is_active' => true,
                    'is_default' => false,
                    'sort_order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (\Throwable) {
            // DB not ready or table missing
        }
    }

    public static function resolve(?string $code): ?self
    {
        if (! static::validateCode($code)) {
            return null;
        }

        $code = strtolower(trim((string) $code));

        try {
            static::ensureDefaultLanguagesExist();
            return static::where('code', $code)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function resolveActive(?string $code): ?self
    {
        if (! static::validateCode($code)) {
            return null;
        }

        $code = strtolower(trim((string) $code));

        try {
            static::ensureDefaultLanguagesExist();
            return static::where('code', $code)->where('is_active', true)->first();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getDefault(): self
    {
        try {
            static::ensureDefaultLanguagesExist();
            $default = static::where('is_default', true)->first();
            if ($default) {
                return $default;
            }

            $id = static::where('code', 'id')->first();
            if ($id) {
                return $id;
            }

            $active = static::where('is_active', true)->orderBy('sort_order')->first();
            if ($active) {
                return $active;
            }
        } catch (\Throwable) {
            // DB not yet ready or table missing in early bootstrap
        }

        $fallback = new static([
            'code' => 'id',
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
            'is_active' => true,
            'is_default' => true,
            'sort_order' => 1,
        ]);

        return $fallback;
    }

    /**
     * @return Collection<int, self>
     */
    public static function getActive(): Collection
    {
        try {
            static::ensureDefaultLanguagesExist();
            return static::where('is_active', true)->orderBy('sort_order')->get();
        } catch (\Throwable) {
            return new Collection([static::getDefault()]);
        }
    }

    /**
     * @return Collection<int, self>
     */
    public static function getAll(): Collection
    {
        try {
            static::ensureDefaultLanguagesExist();
            return static::orderBy('sort_order')->get();
        } catch (\Throwable) {
            return new Collection([static::getDefault()]);
        }
    }

    public static function normalizeCode(?string $code): string
    {
        $lang = static::resolve($code);

        return $lang ? $lang->code : static::getDefault()->code;
    }

    public static function publishedKeyFor(string|self|null $language): string
    {
        $code = $language instanceof self ? $language->code : static::normalizeCode($language);

        return $code === 'id' ? 'homepage_sections' : 'homepage_sections_' . $code;
    }

    public static function draftKeyFor(string|self|null $language): string
    {
        $code = $language instanceof self ? $language->code : static::normalizeCode($language);

        return $code === 'id' ? 'homepage_sections_draft' : 'homepage_sections_' . $code . '_draft';
    }

    public function homepageUrl(bool $isPreview = false): string
    {
        $query = $isPreview ? '?preview=true' : '';

        return $this->is_default ? url('/' . $query) : url('/' . $this->code . $query);
    }

    public static function homepageUrlFor(string|self|null $language, bool $isPreview = false): string
    {
        if ($language instanceof self) {
            return $language->homepageUrl($isPreview);
        }

        $lang = $isPreview ? static::resolve($language) : static::resolveActive($language);
        if ($lang) {
            return $lang->homepageUrl($isPreview);
        }

        $default = static::getDefault();
        return $default->homepageUrl($isPreview);
    }

    public function getUiLabel(string $key, string|array|null $fallback = null, array $replace = []): string
    {
        if (is_array($fallback)) {
            $replace = $fallback;
            $fallback = null;
        }

        $text = null;

        if (! empty($this->ui_labels[$key])) {
            $text = (string) $this->ui_labels[$key];
        } elseif (! $this->is_default) {
            $defaultLang = static::getDefault();
            if (! empty($defaultLang->ui_labels[$key])) {
                $text = (string) $defaultLang->ui_labels[$key];
            }
        }

        if ($text === null) {
            if ($fallback !== null) {
                $text = $fallback;
            } else {
                $translated = __("ui.{$key}", $replace);
                if ($translated !== "ui.{$key}") {
                    return $translated;
                }
                $text = $key;
            }
        }

        if (! empty($replace)) {
            foreach ($replace as $placeholder => $val) {
                $text = str_replace(
                    [':' . $placeholder, ':' . strtoupper($placeholder), ':' . ucfirst($placeholder)],
                    (string) $val,
                    $text
                );
            }
        }

        return $text;
    }
}
