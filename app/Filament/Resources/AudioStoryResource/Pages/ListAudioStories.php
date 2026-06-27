<?php

namespace App\Filament\Resources\AudioStoryResource\Pages;

use App\Filament\Resources\AudioStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAudioStories extends ListRecords
{
    protected static string $resource = AudioStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
