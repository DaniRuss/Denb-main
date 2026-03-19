<?php

namespace App\Filament\Resources\IllegalAssetResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sale';

    protected static ?string $recordTitleAttribute = 'buyer_name';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('buyer_name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('buyer_contact')
                ->maxLength(255),
            Forms\Components\TextInput::make('sale_price')
                ->numeric()
                ->prefix('$')
                ->required(),
            Forms\Components\DatePicker::make('sale_date')
                ->default(now())
                ->required(),
            Forms\Components\Select::make('sold_by_officer_id')
                ->label('Sold By Officer')
                ->relationship('soldByOfficer', 'badge_number')
                ->required(),
            Forms\Components\Textarea::make('notes')
                ->maxLength(8000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('buyer_name')->searchable(),
                Tables\Columns\TextColumn::make('sale_price')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('sale_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('soldByOfficer.badge_number')->label('Officer'),
            ])
            ->defaultSort('sale_date', 'desc')
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
