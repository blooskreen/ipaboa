<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge(),

                TextColumn::make('hours')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),

                IconColumn::make('requires_approval')
                    ->label('Approval')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('produces_certificate')
                    ->label('Cert')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_first_year')
                    ->label('Yr 1')
                    ->boolean()
                    ->toggleable(),

                ToggleColumn::make('is_published')
                    ->label('Published'),

                TextColumn::make('completions_count')
                    ->label('Enrolled')
                    ->counts('completions')
                    ->badge()
                    ->toggleable(),
            ])
            ->defaultSort('title')
            ->filters([
                SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),

                TernaryFilter::make('is_published')
                    ->label('Published'),

                TernaryFilter::make('is_first_year')
                    ->label('First-year'),
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
