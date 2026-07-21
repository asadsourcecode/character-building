<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Jobs\ConvertProductPdfJob;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $pdfChanging = ($data['pdf_path'] ?? null) !== $this->record->pdf_path;

        if ($pdfChanging) {
            $existingExerciseCount = DB::table('exercise_fields')->where('product_id', $this->record->id)->count();

            if ($existingExerciseCount > 0) {
                Notification::make()
                    ->danger()
                    ->title('Cannot replace this book\'s PDF')
                    ->body("A teacher has already placed {$existingExerciseCount} exercise field(s) on this book's pages. Replacing the PDF would re-split the pages and could leave those exercises pointing at the wrong page content. Delete the existing exercises first, or upload the revised PDF as a new product instead.")
                    ->persistent()
                    ->send();

                throw new Halt();
            }

            $data['pdf_conversion_status'] = filled($data['pdf_path'] ?? null) ? 'processing' : null;
            $data['pdf_page_count'] = 0;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('pdf_path') && filled($this->record->pdf_path)) {
            ConvertProductPdfJob::dispatch($this->record);
        }
    }
}
