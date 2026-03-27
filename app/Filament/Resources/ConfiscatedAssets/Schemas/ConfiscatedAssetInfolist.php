<?php

namespace App\Filament\Resources\ConfiscatedAssets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConfiscatedAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('volunteer_tip_id')
                    ->numeric(),
                TextEntry::make('item_description'),
                TextEntry::make('estimated_value')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('seizure_location'),
                TextEntry::make('seized_by')
                    ->numeric(),
                TextEntry::make('seizure_date')
                    ->date(),
                TextEntry::make('handover_status')
                    ->badge(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
