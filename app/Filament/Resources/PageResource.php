<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\RichEditorBlocks\EquationBlock;
use App\Filament\RichEditorBlocks\FigureBlock;
use App\Filament\RichEditorBlocks\ReferenceListBlock;
use App\Models\Page;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationLabel = 'Halaman';
    
    protected static ?int $navigationSort = 4;
    
    protected static function isAdminUser(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isAdminUser();
    }

    public static function canViewAny(): bool
    {
        return static::isAdminUser();
    }



    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Halaman')->schema([
                TextInput::make('title')
                    ->label('Judul Halaman')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Slug')->required()->unique(ignoreRecord: true),
                Placeholder::make('publication_status')
                    ->label('Status Publikasi')
                    ->content(function (?Page $record) {
                        if (! $record) {
                            return new HtmlString('<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Draft (Baru)</span>');
                        }
                        if ($record->is_published) {
                            $date = $record->published_at ? $record->published_at->format('d M Y H:i') : '-';
                            return new HtmlString('<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">● Published (' . e($date) . ')</span>');
                        }
                        return new HtmlString('<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">● Draft (Belum Dipublikasikan)</span>');
                    }),
                Select::make('content_language')
                    ->label('Bahasa Konten')
                    ->options(['id' => 'Indonesia', 'en' => 'Inggris'])
                    ->default('id'),
                Toggle::make('enable_auto_numbering')
                    ->label('Aktifkan Penomoran Otomatis')
                    ->default(true)
                    ->helperText('Jika aktif, judul (H2, H3) akan otomatis diberi nomor urut.'),
                Toggle::make('show_in_header')
                    ->label('Tampilkan di Navigasi Header')
                    ->default(false),
                Toggle::make('show_in_footer')
                    ->label('Tampilkan di Footer')
                    ->default(false),
                Textarea::make('summary')
                    ->label('Ringkasan Singkat')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Ringkasan yang muncul di Halaman Utama atau kartu preview.'),
            ])->columns(2),

            Section::make('Konten Halaman')->schema([
                Builder::make('content_json')
                    ->label('Konten Halaman')
                    ->blocks(\App\Filament\BuilderBlocks::get())
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->live(debounce: 3000)
                    ->afterStateUpdated(function ($livewire) {
                        if (method_exists($livewire, 'save')) {
                            $livewire->save();
                        }
                    })
                    ->helperText('Tersimpan otomatis setiap 3 detik. ✓')
                    ->blockNumbers(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable(),
            Tables\Columns\TextColumn::make('is_published')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (bool $state): string => $state ? 'Published' : 'Draft')
                ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            Tables\Columns\TextColumn::make('published_at')
                ->label('Tanggal Publikasi')
                ->dateTime('d M Y H:i')
                ->placeholder('-')
                ->sortable(),
            Tables\Columns\IconColumn::make('show_in_header')->label('Header')->boolean(),
            Tables\Columns\IconColumn::make('show_in_footer')->label('Footer')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->date('d M Y')->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->since(),
        ])->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Page $record): string => route('page.show', ['page' => $record->slug, 'preview' => 'true']))
                    ->openUrlInNewTab(),
                Action::make('publish')
                    ->label('Publikasikan')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->hidden(fn (Page $record): bool => (bool) $record->is_published)
                    ->requiresConfirmation()
                    ->modalHeading('Publikasikan Halaman')
                    ->modalDescription('Halaman ini akan langsung dapat diakses oleh publik.')
                    ->action(function (Page $record) {
                        $record->update([
                            'is_published' => true,
                            'published_at' => now(),
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Halaman Berhasil Dipublikasikan')
                            ->body("Halaman '{$record->title}' sekarang dapat diakses secara publik.")
                            ->send();
                    }),
                Action::make('unpublish')
                    ->label('Batal Publikasi')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->hidden(fn (Page $record): bool => ! (bool) $record->is_published)
                    ->requiresConfirmation()
                    ->modalHeading('Batal Publikasikan Halaman')
                    ->modalDescription('Halaman ini tidak akan dapat diakses oleh publik lagi.')
                    ->action(function (Page $record) {
                        $record->update([
                            'is_published' => false,
                            'published_at' => null,
                        ]);
                        Notification::make()
                            ->warning()
                            ->title('Halaman Ditarik ke Draft')
                            ->body("Halaman '{$record->title}' telah diubah menjadi draft.")
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
