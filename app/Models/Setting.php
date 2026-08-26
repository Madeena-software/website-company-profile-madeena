<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    public static function normalizeLocale(?string $locale): string
    {
        return Language::normalizeCode($locale);
    }

    public static function homepagePublishedKey(?string $locale = null): string
    {
        return Language::publishedKeyFor($locale);
    }

    public static function homepageDraftKey(?string $locale = null): string
    {
        return Language::draftKeyFor($locale);
    }

    public static function getHomepageSections(?string $locale = null, bool $useDraft = false): array
    {
        $draftKey = static::homepageDraftKey($locale);
        $publishedKey = static::homepagePublishedKey($locale);

        if ($useDraft) {
            return static::getJson($draftKey, static::getJson($publishedKey, [])) ?? [];
        }

        return static::getJson($publishedKey, []) ?? [];
    }

    public static function get(string $key, $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function getJson(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? json_decode($setting->value, true) : $default;
    }

    public static function setJson(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => json_encode($value)]);
    }
}
