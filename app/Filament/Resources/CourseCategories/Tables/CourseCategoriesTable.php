<?php

namespace App\Filament\Resources\CourseCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class CourseCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->color('gray')
                    ->toggleable(),

                TextInputColumn::make('sort_order')
                    ->label('Order')
                    ->type('number')
                    ->rules(['integer', 'min:0']),

                TextColumn::make('courses_count')
                    ->label('Courses')
                    ->counts('courses')
                    ->badge(),
            ])
            ->defaultSort('sort_order')
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
