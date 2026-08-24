<?php

namespace App\Filament\Resources\CourseCompletions\Tables;

use App\Models\CourseCompletion;
use App\Models\Season;
use App\Support\Roles;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CourseCompletionsTable
{
    protected static function approve(CourseCompletion $completion): void
    {
        $hours = (float) $completion->hours_credited;

        if ($hours <= 0) {
            $hours = (float) ($completion->course?->hours ?? 0);
        }

        $completion->forceFill([
            'status'         => CourseCompletion::STATUS_APPROVED,
            'hours_credited' => $hours,
            'approved_at'    => now(),
            'approved_by'    => Auth::id(),
            'completed_at'   => $completion->completed_at ?? now(),
        ])->save();
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('hours_credited')
                    ->label('Hours')
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => CourseCompletion::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        CourseCompletion::STATUS_APPROVED => 'success',
                        CourseCompletion::STATUS_PENDING  => 'warning',
                        default                           => 'gray',
                    }),

                TextColumn::make('season')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('approver.name')
                    ->label('Approved by')
                    ->placeholder('--')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->dateTime('M j, Y')
                    ->placeholder('--')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(CourseCompletion::STATUSES),

                SelectFilter::make('season')
                    ->options(fn () => Season::query()->orderByDesc('started_at')->pluck('label', 'label')->all()),

                SelectFilter::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('This credits the hours toward the member\'s season requirement.')
                    ->visible(fn (CourseCompletion $record) => $record->status !== CourseCompletion::STATUS_APPROVED
                        && (Auth::user()?->hasAnyRole(Roles::CAN_PROMOTE) ?? false))
                    ->action(function (CourseCompletion $record) {
                        static::approve($record);

                        Notification::make()
                            ->title('Approved - ' . $record->fresh()->hours_credited . ' hours credited')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Approve selected')
                        ->icon(Heroicon::OutlinedCheck)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => Auth::user()?->hasAnyRole(Roles::CAN_PROMOTE) ?? false)
                        ->action(function (Collection $records) {
                            $done = 0;

                            foreach ($records as $record) {
                                if ($record->status !== CourseCompletion::STATUS_APPROVED) {
                                    static::approve($record);
                                    $done++;
                                }
                            }

                            Notification::make()
                                ->title($done . ' completion(s) approved')
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasAnyRole(Roles::ALWAYS_FULL) ?? false),
                ]),
            ]);
    }
}
