<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['role'] ?? null) !== 'student' || blank($data['teacher_id'] ?? null)) {
            return $data;
        }

        $subjectIds = $this->getRecord()->subjects()->pluck('subjects.id');

        if ($subjectIds->isEmpty()) {
            return $data;
        }

        $teacherTeachesAnAssignedSubject = User::where('id', $data['teacher_id'])
            ->whereHas('teachingSubjects', fn ($query) => $query->whereIn('subjects.id', $subjectIds))
            ->exists();

        if (! $teacherTeachesAnAssignedSubject) {
            $data['teacher_id'] = null;
        }

        return $data;
    }
}
