<?php

namespace App\Filament\Resources\AudioStoryResource\Pages;

use App\Filament\Resources\AudioStoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAudioStory extends EditRecord
{
    protected static string $resource = AudioStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
