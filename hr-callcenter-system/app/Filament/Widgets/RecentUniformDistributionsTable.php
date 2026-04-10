<?php

namespace App\Filament\Widgets;

use App\Models\UniformDistribution;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;

class RecentUniformDistributionsTable extends TableWidget
{
    protected static ?string $heading = 'Recent Paramilitary Uniform Distributions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UniformDistribution::query()
                    ->with('employee')
                    ->latest('distribution_date')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name_am')
                    ->label('Paramilitary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item_type')
                    ->label('Item')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('size')
                    ->label('Size'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty'),

                Tables\Columns\TextColumn::make('distribution_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distribution_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'success',
                        'replacement' => 'warning',
                        'additional' => 'info',
                        default => 'secondary',
                    }),
            ]);
    }
}
