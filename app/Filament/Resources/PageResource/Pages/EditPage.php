<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected \Filament\Support\Enums\Width | string | null $maxContentWidth = \Filament\Support\Enums\Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Pratinjau')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => route('page.show', ['page' => $this->getRecord()->slug, 'preview' => 'true']))
                ->openUrlInNewTab(),

            Action::make('publish')
                ->label('Publikasikan')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->hidden(fn (): bool => (bool) $this->getRecord()->is_published)
                ->requiresConfirmation()
                ->modalHeading('Publikasikan Halaman')
                ->modalDescription('Halaman ini akan langsung dapat diakses oleh publik.')
                ->action(function () {
                    $this->getRecord()->update([
                        'is_published' => true,
                        'published_at' => now(),
                    ]);
                    $this->refreshFormData(['is_published', 'published_at']);
                    Notification::make()
                        ->success()
                        ->title('Halaman Berhasil Dipublikasikan')
                        ->body("Halaman sekarang dapat diakses secara publik.")
                        ->send();
                }),

            Action::make('unpublish')
                ->label('Batal Publikasi')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('warning')
                ->hidden(fn (): bool => ! (bool) $this->getRecord()->is_published)
                ->requiresConfirmation()
                ->modalHeading('Batal Publikasikan Halaman')
                ->modalDescription('Halaman ini tidak akan dapat diakses oleh publik lagi.')
                ->action(function () {
                    $this->getRecord()->update([
                        'is_published' => false,
                        'published_at' => null,
                    ]);
                    $this->refreshFormData(['is_published', 'published_at']);
                    Notification::make()
                        ->warning()
                        ->title('Halaman Ditarik ke Draft')
                        ->body("Halaman telah diubah menjadi draft.")
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
