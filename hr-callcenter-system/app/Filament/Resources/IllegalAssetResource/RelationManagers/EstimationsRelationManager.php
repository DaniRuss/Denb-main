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

class EstimationsRelationManager extends RelationManager
{
    protected static string $relationship = 'estimations';

    protected static ?string $recordTitleAttribute = 'estimated_value';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('estimated_value')
                ->numeric()
                ->prefix('$')
                ->required(),
            Forms\Components\TextInput::make('evaluator_name')
                ->required()
                ->maxLength(255),
            Forms\Components\DatePicker::make('evaluation_date')
                ->default(now())
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
                Tables\Columns\TextColumn::make('estimated_value')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('evaluator_name')->searchable(),
                Tables\Columns\TextColumn::make('evaluation_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('evaluation_date', 'desc')
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
