<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Quiz;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Select::make('course_id')
                    ->label('Attached course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->helperText('Optional. Leave blank for a standalone test.'),

                Select::make('reveal_answers')
                    ->label('What members see after submitting')
                    ->options(Quiz::REVEAL_OPTIONS)
                    ->default(Quiz::REVEAL_WRONG)
                    ->required(),

                TextInput::make('passing_percentage')
                    ->label('Passing score (%)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->default(70)
                    ->required(),

                TextInput::make('max_attempts')
                    ->label('Maximum attempts')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Leave blank for unlimited.'),

                TextInput::make('time_limit_minutes')
                    ->label('Time limit (minutes)')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Leave blank for untimed. Enforced on the server, not just in the browser.'),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('instructions')
                    ->rows(3)
                    ->helperText('Shown to the member before they begin.')
                    ->columnSpanFull(),

                Toggle::make('shuffle_questions')
                    ->label('Shuffle question order'),

                Toggle::make('produces_certificate')
                    ->label('Issue a certificate on pass'),

                Toggle::make('is_published')
                    ->helperText('Members can only see and take published quizzes.'),
            ]);
    }
}
