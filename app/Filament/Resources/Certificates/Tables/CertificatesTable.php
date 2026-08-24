<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Models\Certificate;
use App\Support\Roles;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('certifiable_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Certificate $record) => $record->sourceLabel()),

                TextColumn::make('serial')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('issued_at')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('issued_at', 'desc')
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (Certificate $record) => route('certificates.download', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasAnyRole(Roles::ALWAYS_FULL) ?? false),
                ]),
            ]);
    }
}
