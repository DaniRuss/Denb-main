<?php

namespace App\Filament\Resources\EscalationLevels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EscalationLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('level')
                    ->required()
                    ->numeric(),
                TextInput::make('response_time_hours')
                    ->required()
                    ->numeric(),
                TextInput::make('resolution_time_hours')
                    ->required()
                    ->numeric(),
                Textarea::make('permissions')
                    ->columnSpanFull(),
            ]);
    }
}
