<?php

namespace App\Filament\Resources\IllegalAssetResource\RelationManagers;

use App\Models\AssetActivity;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'action';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date/Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Performed By'),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Registered'  => 'primary',
                        'Handed Over' => 'warning',
                        'Estimated'   => 'info',
                        'Transferred' => 'gray',
                        'Sold'        => 'success',
                        'Disposed'    => 'danger',
                        'Edited'      => 'warning',
                        default       => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
