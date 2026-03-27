<?php

namespace App\Filament\Resources\ConfiscatedAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class ConfiscatedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('volunteer_tip_id')
                    ->relationship('volunteerTip', 'tip_code')
                    ->label('Linked Tip Code')
                    ->default(request()->query('tip_id'))
                    ->required(),
                TextInput::make('item_description')
                    ->label('Item Description (የተያዘ ዕቃ)')
                    ->required(),
                TextInput::make('estimated_value')
                    ->label('Estimated Value')
                    ->numeric(),
                TextInput::make('seizure_location')
                    ->label('Seizure Location (የተያዘበት ቦታ)')
                    ->required(),
                Hidden::make('seized_by')
                    ->default(fn() => auth()->id()),
                DatePicker::make('seizure_date')
                    ->label('Seizure Date (የተያዘበት ቀን)')
                    ->default(now())
                    ->required(),
                Select::make('handover_status')
                    ->label('Status')
                    ->options([
                        'impounded' => 'Impounded',
                        'auctioned' => 'Auctioned',
                        'destroyed' => 'Destroyed',
                        'returned' => 'Returned',
                    ])
                    ->default('impounded')
                    ->required(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
    }
}
