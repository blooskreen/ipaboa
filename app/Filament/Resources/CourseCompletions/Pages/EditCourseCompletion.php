<?php

namespace App\Filament\Resources\CourseCompletions\Pages;

use App\Filament\Resources\CourseCompletions\CourseCompletionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseCompletion extends EditRecord
{
    protected static string $resource = CourseCompletionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
