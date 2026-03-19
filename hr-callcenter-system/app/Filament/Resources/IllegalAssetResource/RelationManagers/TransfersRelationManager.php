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

class TransfersRelationManager extends RelationManager
{
    protected static string $relationship = 'transfers';

    protected static ?string $recordTitleAttribute = 'transfer_date';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('from_department_id')
                ->label('From Department')
                ->relationship('fromDepartment', 'name_en')
                ->required(),
            Forms\Components\Select::make('to_department_id')
                ->label('To Department')
                ->relationship('toDepartment', 'name_en')
                ->required(),
            Forms\Components\TextInput::make('from_storage_facility')->maxLength(255),
            Forms\Components\TextInput::make('to_storage_facility')->maxLength(255),
            Forms\Components\Select::make('transferred_by_officer_id')
                ->label('Transferred By')
                ->relationship('transferredByOfficer', 'badge_number')
                ->required(),
            Forms\Components\DatePicker::make('transfer_date')
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
                Tables\Columns\TextColumn::make('fromDepartment.name_en')->label('From')->searchable(),
                Tables\Columns\TextColumn::make('toDepartment.name_en')->label('To')->searchable(),
                Tables\Columns\TextColumn::make('transfer_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('transferredByOfficer.badge_number')->label('Officer'),
            ])
            ->defaultSort('transfer_date', 'desc')
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
