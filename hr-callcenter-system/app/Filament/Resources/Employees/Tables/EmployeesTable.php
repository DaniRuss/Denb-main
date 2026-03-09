<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('employee_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('full_name_am')
                    ->label('Name (አማርኛ)')
                    ->getStateUsing(fn($record) => "{$record->first_name_am} {$record->last_name_am}")
                    ->searchable(['first_name_am', 'last_name_am']),

                \Filament\Tables\Columns\TextColumn::make('full_name_en')
                    ->label('Name (English)')
                    ->getStateUsing(fn($record) => "{$record->first_name_en} {$record->last_name_en}")
                    ->searchable(['first_name_en', 'last_name_en'])
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'on_leave' => 'warning',
                        'terminated' => 'danger',
                        default => 'secondary',
                    }),

                \Filament\Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                        'terminated' => 'Terminated',
                    ]),
                \Filament\Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
