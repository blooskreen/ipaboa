<?php

namespace App\Filament\Resources\CourseCompletions;

use App\Filament\Resources\CourseCompletions\Pages\CreateCourseCompletion;
use App\Filament\Resources\CourseCompletions\Pages\EditCourseCompletion;
use App\Filament\Resources\CourseCompletions\Pages\ListCourseCompletions;
use App\Filament\Resources\CourseCompletions\Schemas\CourseCompletionForm;
use App\Filament\Resources\CourseCompletions\Tables\CourseCompletionsTable;
use App\Models\CourseCompletion;
use App\Models\SectionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CourseCompletionResource extends Resource
{
    protected static ?string $model = CourseCompletion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?string $navigationLabel = 'Training Hours';

    protected static ?string $modelLabel = 'completion';

    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Education';
    }

    public static function canAccess(): bool
    {
        return SectionAccess::allows('Education');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CourseCompletion::query()
            ->where('status', CourseCompletion::STATUS_PENDING)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CourseCompletionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseCompletionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCourseCompletions::route('/'),
            'create' => CreateCourseCompletion::route('/create'),
            'edit'   => EditCourseCompletion::route('/{record}/edit'),
        ];
    }
}
