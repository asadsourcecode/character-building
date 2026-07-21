<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Jobs\ConvertProductPdfJob;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['pdf_path'] ?? null)) {
            $data['pdf_conversion_status'] = 'processing';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if (filled($this->record->pdf_path)) {
            ConvertProductPdfJob::dispatch($this->record);
        }
    }
}
