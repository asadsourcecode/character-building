<?php

namespace App\Filament\Resources\FrontendLogResource\Pages;

use App\Filament\Resources\FrontendLogResource;
use Filament\Resources\Pages\ListRecords;

class ListFrontendLogs extends ListRecords
{
    protected static string $resource = FrontendLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
