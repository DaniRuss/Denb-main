<?php

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('name_am')
                    ->label('Name (አማርኛ)')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('name_en')
                    ->label('Name (English)')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('headOfDepartment.name')
                    ->label('Head of Department')
                    ->default('Not Assigned'),

                \Filament\Tables\Columns\TextColumn::make('officers_count')
                    ->label('Officers')
                    ->counts('officers'),

                \Filament\Tables\Columns\TextColumn::make('complaints_count')
                    ->label('Active Cases')
                    ->counts('complaints'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
