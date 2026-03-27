<?php

namespace App\Filament\Resources\ConfiscatedAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConfiscatedAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('volunteer_tip_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('item_description')
                    ->searchable(),
                TextColumn::make('estimated_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('seizure_location')
                    ->searchable(),
                TextColumn::make('seized_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('seizure_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('handover_status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
