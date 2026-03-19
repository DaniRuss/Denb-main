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

class DisposalsRelationManager extends RelationManager
{
    protected static string $relationship = 'disposal';

    protected static ?string $recordTitleAttribute = 'disposal_method';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('disposal_method')
                ->options([
                    'Destruction' => 'Destruction',
                    'Recycling' => 'Recycling',
                    'Government storage' => 'Government storage',
                    'Other' => 'Other',
                ])
                ->required(),
            Forms\Components\DatePicker::make('disposal_date')
                ->default(now())
                ->required(),
            Forms\Components\Select::make('disposed_by_officer_id')
                ->label('Disposed By Officer')
                ->relationship('disposedByOfficer', 'badge_number')
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
                Tables\Columns\TextColumn::make('disposal_method')->badge(),
                Tables\Columns\TextColumn::make('disposal_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('disposedByOfficer.badge_number')->label('Officer'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('disposal_date', 'desc')
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
