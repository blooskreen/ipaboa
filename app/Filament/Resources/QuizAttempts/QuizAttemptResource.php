<?php

namespace App\Filament\Resources\QuizAttempts;

use App\Filament\Resources\QuizAttempts\Pages\ListQuizAttempts;
use App\Filament\Resources\QuizAttempts\Tables\QuizAttemptsTable;
use App\Models\QuizAttempt;
use App\Models\SectionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class QuizAttemptResource extends Resource
{
    protected static ?string $model = QuizAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Quiz Results';

    protected static ?string $modelLabel = 'quiz result';

    protected static ?string $pluralModelLabel = 'quiz results';

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Education';
    }

    public static function canAccess(): bool
    {
        return SectionAccess::allows('Education');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** Shows the grading backlog right in the sidebar. */
    public static function getNavigationBadge(): ?string
    {
        $count = QuizAttempt::query()->pendingReview()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return QuizAttemptsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuizAttempts::route('/'),
        ];
    }
}
