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

class HandoversRelationManager extends RelationManager
{
    protected static string $relationship = 'handovers';

    protected static ?string $recordTitleAttribute = 'handover_date';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('department_id')
                ->label('To Department')
                ->relationship('department', 'name_en')
                ->required(),
            Forms\Components\Select::make('handed_over_to_officer_id')
                ->label('To Officer')
                ->relationship('handedOverToOfficer', 'badge_number')
                ->required(),
            Forms\Components\DatePicker::make('handover_date')
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
                Tables\Columns\TextColumn::make('department.name_en')->label('Department'),
                Tables\Columns\TextColumn::make('handedOverToOfficer.badge_number')->label('Officer Badge'),
                Tables\Columns\TextColumn::make('handover_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('notes')->limit(50),
            ])
            ->defaultSort('handover_date', 'desc')
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
