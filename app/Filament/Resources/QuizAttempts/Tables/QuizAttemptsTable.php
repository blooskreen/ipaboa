<?php

namespace App\Filament\Resources\QuizAttempts\Tables;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Support\QuizGrader;
use App\Support\Roles;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class QuizAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('attempt_number')
                    ->label('#')
                    ->alignEnd(),

                TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn ($state, QuizAttempt $record) => rtrim(rtrim((string) $state, '0'), '.') . ' / ' . $record->total_points)
                    ->alignEnd(),

                TextColumn::make('percentage')
                    ->label('%')
                    ->suffix('%')
                    ->sortable()
                    ->alignEnd(),

                IconColumn::make('passed')
                    ->boolean(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => QuizAttempt::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        QuizAttempt::STATUS_PENDING_REVIEW => 'warning',
                        QuizAttempt::STATUS_GRADED         => 'success',
                        default                            => 'gray',
                    }),

                TextColumn::make('submitted_at')
                    ->dateTime('M j, Y g:ia')
                    ->placeholder('--')
                    ->sortable(),

                TextColumn::make('grader.name')
                    ->label('Graded by')
                    ->placeholder('--')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(QuizAttempt::STATUSES),

                SelectFilter::make('quiz_id')
                    ->label('Quiz')
                    ->relationship('quiz', 'title')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('passed'),
            ])
            ->recordActions([
                Action::make('grade')
                    ->label('Grade')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('warning')
                    ->modalHeading('Grade short answers')
                    ->modalSubmitActionLabel('Save grade')
                    ->visible(fn (QuizAttempt $record) => $record->status === QuizAttempt::STATUS_PENDING_REVIEW
                        && (Auth::user()?->hasAnyRole(Quiz::graderRoles()) ?? false))
                    ->schema(function (QuizAttempt $record) {
                        $quiz    = $record->quiz()->with('questions')->first();
                        $answers = (array) $record->answers;
                        $fields  = [];

                        foreach ($quiz?->questions ?? [] as $question) {
                            if ($question->isAutoGradable()) {
                                continue;
                            }

                            $given = $answers[$question->id] ?? '';

                            if (is_array($given)) {
                                $given = implode(', ', $given);
                            }

                            $given = trim((string) $given);

                            $fields[] = TextInput::make('points.' . $question->id)
                                ->label($question->prompt)
                                ->helperText('Answer given: ' . ($given !== '' ? $given : '(left blank)')
                                    . '  --  maximum ' . $question->points . ' point(s)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue($question->points)
                                ->default(0)
                                ->required();
                        }

                        return $fields;
                    })
                    ->action(function (array $data, QuizAttempt $record) {
                        QuizGrader::applyManualScores(
                            $record,
                            $data['points'] ?? [],
                            Auth::id(),
                        );

                        $record->refresh();

                        Notification::make()
                            ->title($record->passed ? 'Graded - passed' : 'Graded - did not pass')
                            ->body($record->percentage . '% (' . $record->score . ' of ' . $record->total_points . ')')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasAnyRole(Roles::ALWAYS_FULL) ?? false),
                ]),
            ]);
    }
}
