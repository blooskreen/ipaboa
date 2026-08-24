<?php

namespace App\Filament\Resources\Seasons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('However your organization names it, e.g. "2026-27" or "Fall 2026".'),

                Toggle::make('is_current')
                    ->label('This is the current season')
                    ->helperText('Turning this on automatically turns it off for every other season.'),

                DatePicker::make('started_at')
                    ->native(false),

                DatePicker::make('ended_at')
                    ->native(false)
                    ->afterOrEqual('started_at'),
            ]);
    }
}
