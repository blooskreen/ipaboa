<?php

namespace App\Filament\Resources\Quizzes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QuizzesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->placeholder('Standalone')
                    ->toggleable(),

                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->badge(),

                TextColumn::make('passing_percentage')
                    ->label('Pass %')
                    ->suffix('%')
                    ->alignEnd(),

                TextColumn::make('max_attempts')
                    ->label('Attempts')
                    ->placeholder('Unlimited')
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('time_limit_minutes')
                    ->label('Time')
                    ->suffix(' min')
                    ->placeholder('Untimed')
                    ->alignEnd()
                    ->toggleable(),

                IconColumn::make('produces_certificate')
                    ->label('Cert')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('attempts_count')
                    ->label('Taken')
                    ->counts('attempts')
                    ->badge()
                    ->toggleable(),

                ToggleColumn::make('is_published')
                    ->label('Published'),
            ])
            ->defaultSort('title')
            ->filters([
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
