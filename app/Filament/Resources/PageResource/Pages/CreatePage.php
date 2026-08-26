<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected \Filament\Support\Enums\Width | string | null $maxContentWidth = \Filament\Support\Enums\Width::Full;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_published'] = false;
        $data['published_at'] = null;

        return $data;
    }
}
