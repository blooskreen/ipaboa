<?php

namespace App\Filament\Resources\CourseCompletions\Pages;

use App\Filament\Resources\CourseCompletions\CourseCompletionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseCompletions extends ListRecords
{
    protected static string $resource = CourseCompletionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
