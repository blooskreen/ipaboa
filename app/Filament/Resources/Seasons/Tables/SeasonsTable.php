<?php

namespace App\Filament\Resources\Seasons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean(),

                TextColumn::make('started_at')
                    ->date('M j, Y')
                    ->placeholder('--')
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->date('M j, Y')
                    ->placeholder('--')
                    ->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
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
