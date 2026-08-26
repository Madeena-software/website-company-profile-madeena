<?php

namespace App\Filament\Pages;

use App\Filament\BuilderBlocks;
use App\Models\Language;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class HomepageEditor extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Halaman Utama';
    protected static ?string $title = 'Edit Halaman Utama';
    protected static ?int $navigationSort = 1;

    protected static function isAdminUser(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user instanceof \App\Models\User && $user->isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isAdminUser();
    }

    public static function canAccess(): bool
    {
        return static::isAdminUser();
    }

    public function getMaxContentWidth(): \Filament\Support\Enums\Width | string | null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected string $view = 'filament.pages.homepage-editor';

    public ?array $data = [];
    public string $activeLocale = 'id';

    public function mount(): void
    {
        $requestedLocale = request()->query('lang', Language::getDefault()->code);
        $this->loadLanguageState($requestedLocale);
    }

    public function updatedActiveLocale($value): void
    {
        $this->switchLanguage($value);
    }

    public function switchLanguage(string $locale): void
    {
        $this->loadLanguageState($locale);
    }

    public function setLanguage(string $locale): void
    {
        $this->switchLanguage($locale);
    }

    public function loadLanguageState(string $locale): void
    {
        $this->activeLocale = Language::normalizeCode($locale);
        $sections = Setting::getHomepageSections($this->activeLocale, true);

        $this->form->fill([
            'sections' => $sections,
        ]);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Builder::make('sections')
                    ->label('Bagian Halaman')
                    ->blocks(BuilderBlocks::get())
                    ->blockPickerColumns(3)
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->addActionLabel('+ Tambah Bagian Baru')
                    ->blockNumbers(false)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('👁️ Pratinjau')
                ->icon('heroicon-o-eye')
                ->url(fn () => Language::homepageUrlFor($this->activeLocale, true))
                ->openUrlInNewTab()
                ->color('info'),

            Action::make('duplicate_to_language')
                ->label('📋 Duplikat ke Bahasa Lain')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->modalHeading('Duplikat Halaman Utama ke Bahasa Lain')
                ->modalDescription('Draft bahasa target akan dibuat dari versi sumber (draft jika ada, atau versi publik). Simpan draft sumber terlebih dahulu jika ingin menyertakan perubahan terbaru.')
                ->modalSubmitActionLabel('Duplikat sebagai Draft')
                ->form([
                    \Filament\Forms\Components\Placeholder::make('source_info')
                        ->label('Bahasa Sumber')
                        ->content(function () {
                            $sourceLang = Language::resolve($this->activeLocale);
                            return $sourceLang
                                ? "{$sourceLang->native_name} ({$sourceLang->code})"
                                : strtoupper($this->activeLocale);
                        }),

                    \Filament\Forms\Components\Select::make('target_language')
                        ->label('Pilih Bahasa Target')
                        ->required()
                        ->options(function () {
                            $currentCode = Language::normalizeCode($this->activeLocale);
                            return Language::getAll()
                                ->filter(fn (Language $l) => $l->code !== $currentCode)
                                ->mapWithKeys(function (Language $l) {
                                    $status = $l->is_active ? 'Aktif' : 'Nonaktif';
                                    $draftKey = Language::draftKeyFor($l->code);
                                    $publishedKey = Language::publishedKeyFor($l->code);
                                    $hasContent = Setting::getJson($draftKey) !== null || Setting::getJson($publishedKey) !== null;
                                    $suffix = $hasContent ? ' (Sudah Ada Versi)' : '';
                                    return [$l->code => "{$l->native_name} ({$l->code}) - {$status}{$suffix}"];
                                })
                                ->toArray();
                        })
                        ->helperText('Hanya bahasa terdaftar yang belum memiliki versi homepage yang dapat disalin.'),
                ])
                ->action(function (array $data) {
                    $this->duplicateToLanguage($data['target_language'] ?? null);
                }),

            Action::make('save')
                ->label('💾 Simpan Draft')
                ->icon('heroicon-o-check')
                ->action('save')
                ->color('warning'),

            Action::make('publish_to_prod')
                ->label('🚀 Update Prod')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Update Website Live')
                ->modalDescription(new \Illuminate\Support\HtmlString('Apakah Anda yakin ingin menerapkan perubahan draft ini ke website utama? Pengunjung akan langsung melihat perubahan ini.'))
                ->action(function () {
                    $this->publish();
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $locale = Language::normalizeCode($this->activeLocale ?? ($state['locale'] ?? null));
        $this->activeLocale = $locale;
        $draftKey = Language::draftKeyFor($locale);

        $sections = array_values($state['sections'] ?? []);
        Setting::setJson($draftKey, $sections);

        $langObj = Language::resolve($locale);
        $langLabel = $langObj ? "{$langObj->native_name} ({$langObj->code})" : strtoupper($locale);

        Notification::make()
            ->success()
            ->title("Draft ({$langLabel}) Berhasil Disimpan")
            ->body("Perubahan bahasa {$langLabel} telah disimpan sebagai draft. Gunakan Pratinjau untuk melihat perubahan.")
            ->send();
    }

    public function publish(): void
    {
        $state = $this->form->getState();
        $locale = Language::normalizeCode($this->activeLocale ?? ($state['locale'] ?? null));
        $this->activeLocale = $locale;
        $draftKey = Language::draftKeyFor($locale);
        $publishedKey = Language::publishedKeyFor($locale);

        $draft = Setting::getJson($draftKey, null);
        
        if (is_null($draft)) {
            $draft = Setting::getJson($publishedKey, []);
        }

        $sections = array_values($draft ?? []);
        Setting::setJson($publishedKey, $sections);

        $langObj = Language::resolve($locale);
        $langLabel = $langObj ? "{$langObj->native_name} ({$langObj->code})" : strtoupper($locale);

        Notification::make()
            ->success()
            ->title("Berhasil Diupdate ({$langLabel})")
            ->body("Halaman utama ({$langLabel}) telah berhasil diterapkan ke website live.")
            ->send();
    }

    public static function getNavigation(bool $useDraft = false, ?string $language = null): array
    {
        $locale = Language::normalizeCode($language);
        $sections = Setting::getHomepageSections($locale, $useDraft);
        $navItems = [];

        foreach ($sections as $section) {
            if (! empty($section['data']['show_in_nav'])) {
                $sectionId = $section['data']['section_id'] ?? '';
                $navItems[] = [
                    'label'       => $section['data']['nav_label'] ?? ucfirst($section['type']),
                    'anchor'      => $sectionId ? '#' . $sectionId : null,
                    'is_external' => false,
                ];
            }
        }

        // Append custom links from settings
        $customLinks = Setting::getJson('nav_custom_links', []);
        foreach ($customLinks as $link) {
            $navItems[] = [
                'label'       => $link['label'] ?? '',
                'url'         => $link['url'] ?? '#',
                'is_external' => true,
            ];
        }

        return $navItems;
    }

    public function duplicateToLanguage(?string $targetLocale): void
    {
        $sourceLocale = Language::normalizeCode($this->activeLocale);
        $targetCode = $targetLocale ? strtolower(trim((string) $targetLocale)) : null;

        if (! $targetCode || ! Language::validateCode($targetCode)) {
            Notification::make()
                ->danger()
                ->title('Bahasa Target Tidak Valid')
                ->body('Bahasa target yang dipilih tidak valid atau tidak terdaftar.')
                ->send();
            return;
        }

        $targetLang = Language::resolve($targetCode);
        if (! $targetLang) {
            Notification::make()
                ->danger()
                ->title('Bahasa Target Tidak Terdaftar')
                ->body('Bahasa target tidak ditemukan dalam sistem.')
                ->send();
            return;
        }

        if ($sourceLocale === $targetLang->code) {
            Notification::make()
                ->danger()
                ->title('Bahasa Target Tidak Boleh Sama')
                ->body('Bahasa sumber dan bahasa target tidak boleh sama.')
                ->send();
            return;
        }

        // Target protection: Block if target draft or target published already exists
        $targetDraftKey = Language::draftKeyFor($targetLang->code);
        $targetPublishedKey = Language::publishedKeyFor($targetLang->code);

        if (Setting::getJson($targetDraftKey) !== null || Setting::getJson($targetPublishedKey) !== null) {
            Notification::make()
                ->warning()
                ->title('Versi Target Sudah Ada')
                ->body("Versi homepage untuk {$targetLang->native_name} ({$targetLang->code}) sudah ada. Silakan edit versi tersebut.")
                ->send();
            return;
        }

        // Resolve source content: persisted draft first, then published fallback
        $sourceDraftKey = Language::draftKeyFor($sourceLocale);
        $sourcePublishedKey = Language::publishedKeyFor($sourceLocale);

        $sourceSections = Setting::getJson($sourceDraftKey);
        if ($sourceSections === null) {
            $sourceSections = Setting::getJson($sourcePublishedKey);
        }

        if ($sourceSections === null || (is_array($sourceSections) && empty($sourceSections))) {
            Notification::make()
                ->warning()
                ->title('Konten Sumber Kosong')
                ->body('Tidak ada konten sumber yang dapat diduplikasi.')
                ->send();
            return;
        }

        // Write ONLY target draft
        $duplicatedSections = array_values($sourceSections);
        Setting::setJson($targetDraftKey, $duplicatedSections);

        // Switch editor active language and reload form
        $this->activeLocale = $targetLang->code;
        $this->form->fill([
            'sections' => $duplicatedSections,
        ]);

        $sourceLang = Language::resolve($sourceLocale);
        $sourceLabel = $sourceLang ? $sourceLang->native_name : strtoupper($sourceLocale);

        Notification::make()
            ->success()
            ->title("Draft {$targetLang->native_name} Berhasil Dibuat")
            ->body("Draft {$targetLang->native_name} berhasil dibuat dari {$sourceLabel}. Silakan terjemahkan dan tinjau sebelum dipublikasikan.")
            ->send();
    }
}
