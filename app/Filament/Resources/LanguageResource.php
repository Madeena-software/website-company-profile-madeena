<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageResource\Pages;
use App\Models\Language;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Bahasa';

    protected static ?string $pluralModelLabel = 'Bahasa';

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
            Section::make('Informasi Bahasa')
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Bahasa (e.g. id, en, ja, pt-br)')
                        ->required()
                        ->maxLength(10)
                        ->regex('/^[a-z]{2,3}(-[a-z0-9]{2,4})?$/')
                        ->disabled(fn ($record) => $record !== null)
                        ->unique(ignoreRecord: true)
                        ->helperText('Kode bahasa tidak dapat diubah setelah dibuat.'),

                    TextInput::make('name')
                        ->label('Nama Bahasa (English / System)')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('native_name')
                        ->label('Nama Asli (Native Name, e.g. Bahasa Indonesia, 日本語)')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('sort_order')
                        ->label('Urutan Tampilan')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(false)
                        ->disabled(fn ($record) => $record?->is_default ?? false)
                        ->helperText('Bahasa default harus selalu aktif.'),

                    KeyValue::make('ui_labels')
                        ->label('Label UI / Terjemahan Antarmuka (JSON)')
                        ->keyLabel('Kunci (e.g. navigation, contact, language)')
                        ->valueLabel('Teks Terjemahan')
                        ->columnSpanFull()
                        ->helperText('Label antarmuka umum (navigation, contact, all_rights_reserved, preview_mode_notice, back, language, read, view_all, articles, manage_website_in_admin).'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('native_name')->label('Nama Asli')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('is_default')->label('Default')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->since(),
        ])->defaultSort('sort_order')
            ->filters([])
            ->actions([
                Action::make('set_default')
                    ->label('Jadikan Default')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn (Language $record) => $record->is_default)
                    ->requiresConfirmation()
                    ->modalHeading('Jadikan Bahasa Default')
                    ->modalDescription(fn (Language $record) => "Apakah Anda yakin ingin menjadikan {$record->native_name} ({$record->code}) sebagai bahasa default homepage?")
                    ->action(fn (Language $record) => $record->setAsDefault()),
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
