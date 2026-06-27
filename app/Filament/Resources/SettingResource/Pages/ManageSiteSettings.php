<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\EditRecord;

class ManageSiteSettings extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    public function mount(int|string|null $record = null): void
    {
        $this->record = Setting::query()->firstOrCreate(
            ['key' => 'site_settings'],
            [
                'group' => 'general',
                'type' => 'site',
                'status' => true,
            ],
        );

        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
