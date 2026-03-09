<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UniformDistributionRelationManager extends RelationManager
{
    protected static string $relationship = 'uniformDistributions';

    protected static ?string $recordTitleAttribute = 'item_type';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('item_type')
                    ->label('Item Type')
                    ->options([
                        'shirt' => 'Shirt',
                        'pant' => 'Pant',
                        'jacket' => 'Jacket',
                        'rain_coat' => 'Rain Coat',
                        't_shirt' => 'T-Shirt',
                        'hat' => 'Hat',
                        'shoe_casual' => 'Shoe Casual',
                        'shoe_leather' => 'Shoe Leather',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('size')
                    ->label('Size')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->minValue(1),

                Forms\Components\DatePicker::make('distribution_date')
                    ->label('Distribution Date')
                    ->default(now())
                    ->required(),

                Forms\Components\Select::make('distribution_type')
                    ->label('Distribution Type')
                    ->options([
                        'new' => 'New Issue',
                        'replacement' => 'Replacement',
                        'additional' => 'Additional',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('reason')
                    ->label('Reason')
                    ->maxLength(255),

                Forms\Components\Select::make('issued_by')
                    ->label('Issued By')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->default(auth()->id())
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
