<?php

namespace App\Filament\Resources\CourseCompletions\Schemas;

use App\Models\Course;
use App\Models\CourseCompletion;
use App\Models\Season;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CourseCompletionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Member')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $course = $state ? Course::find($state) : null;

                        if ($course) {
                            $set('hours_credited', (string) $course->hours);
                        }
                    }),

                Select::make('status')
                    ->options(CourseCompletion::STATUSES)
                    ->default(CourseCompletion::STATUS_APPROVED)
                    ->required(),

                TextInput::make('hours_credited')
                    ->label('Hours credited')
                    ->numeric()
                    ->step('0.25')
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('Auto-filled from the course, but you can override it.'),

                Select::make('season')
                    ->options(fn () => Season::query()->orderByDesc('started_at')->pluck('label', 'label')->all())
                    ->default(fn () => Season::currentLabel())
                    ->required()
                    ->helperText('Hours count toward this season only.'),

                DateTimePicker::make('completed_at')
                    ->native(false)
                    ->default(now()),
            ]);
    }
}
