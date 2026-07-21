<?php

namespace App\Filament\Resources\ProductSectionResource\Pages;

use App\Filament\Resources\ProductSectionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProductSection extends EditRecord
{
    protected static string $resource = ProductSectionResource::class;

    protected function getHeaderActions(): array
    {
        $previewUrl = rtrim(env('FRONTEND_URL', 'http://localhost:8001'), '/') . '/pricing/preview';

        return [
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url($previewUrl)
                ->openUrlInNewTab(),

            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('success')
                ->hidden(fn () => (bool) $this->record->is_published)
                ->requiresConfirmation()
                ->modalHeading('Publish this section?')
                ->modalDescription('This will make the section visible on the frontend immediately.')
                ->modalSubmitActionLabel('Publish')
                ->action(function () {
                    $this->record->update(['is_published' => true]);
                    Notification::make()
                        ->title('Section published')
                        ->body('It is now visible on the frontend.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->hidden(fn () => ! (bool) $this->record->is_published)
                ->requiresConfirmation()
                ->modalHeading('Unpublish this section?')
                ->modalDescription('This will hide the section from the frontend immediately.')
                ->modalSubmitActionLabel('Unpublish')
                ->action(function () {
                    $this->record->update(['is_published' => false]);
                    Notification::make()
                        ->title('Section unpublished')
                        ->body('It is now hidden from the frontend.')
                        ->warning()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
