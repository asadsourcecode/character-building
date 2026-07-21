<?php

namespace App\Filament\Resources\ProductSectionResource\Pages;

use App\Filament\Resources\ProductSectionResource;
use App\Models\ProductSection;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSection extends CreateRecord
{
    protected static string $resource = ProductSectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['sort_order'])) {
            $data['sort_order'] = (ProductSection::max('sort_order') ?? 0) + 1;
        }

        return $data;
    }
}
