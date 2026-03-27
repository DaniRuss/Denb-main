<?php

namespace App\Filament\Resources\ConfiscatedAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ConfiscatedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('volunteer_tip_id')
                    ->required()
                    ->numeric(),
                TextInput::make('item_description')
                    ->required(),
                TextInput::make('estimated_value')
                    ->numeric(),
                TextInput::make('seizure_location')
                    ->required(),
                TextInput::make('seized_by')
                    ->required()
                    ->numeric(),
                DatePicker::make('seizure_date')
                    ->required(),
                Select::make('handover_status')
                    ->options([
            'impounded' => 'Impounded',
            'auctioned' => 'Auctioned',
            'destroyed' => 'Destroyed',
            'returned' => 'Returned',
        ])
                    ->default('impounded')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
