<?php

namespace App\Filament\Resources\PenaltyScheduleResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ViolationTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'violationTypes';

    protected static ?string $recordTitleAttribute = 'name_am';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('code')
                ->label(app()->getLocale() === 'am' ? 'ኮድ' : 'Code')
                ->maxLength(20)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name_am')
                ->label(app()->getLocale() === 'am' ? 'ስም (አማርኛ)' : 'Name (Amharic)')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('name_en')
                ->label(app()->getLocale() === 'am' ? 'ስም (እንግሊዝኛ)' : 'Name (English)')
                ->maxLength(255),
            Forms\Components\TextInput::make('regulation_reference')
                ->label(app()->getLocale() === 'am' ? 'ደንብ ማጣቀሻ' : 'Regulation Reference')
                ->maxLength(255),
            Forms\Components\TextInput::make('fine_amount')
                ->label(app()->getLocale() === 'am' ? 'የቅጣት መጠን (ብር)' : 'Fine Amount (Birr)')
                ->numeric()
                ->required()
                ->prefix('ETB'),
            Forms\Components\TextInput::make('min_fine')
                ->label(app()->getLocale() === 'am' ? 'ዝቅተኛ ቅጣት' : 'Minimum Fine')
                ->numeric()
                ->prefix('ETB'),
            Forms\Components\TextInput::make('max_fine')
                ->label(app()->getLocale() === 'am' ? 'ከፍተኛ ቅጣት' : 'Maximum Fine')
                ->numeric()
                ->prefix('ETB'),
            Forms\Components\Toggle::make('is_active')
                ->label(app()->getLocale() === 'am' ? 'ንቁ' : 'Active')
                ->default(true),
            Forms\Components\Textarea::make('description')
                ->label(app()->getLocale() === 'am' ? 'ማብራሪያ' : 'Description')
                ->maxLength(5000)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(app()->getLocale() === 'am' ? 'ኮድ' : 'Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_am')
                    ->label(app()->getLocale() === 'am' ? 'ስም' : 'Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('fine_amount')
                    ->label(app()->getLocale() === 'am' ? 'ቅጣት (ብር)' : 'Fine (Birr)')
                    ->money('ETB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('regulation_reference')
                    ->label(app()->getLocale() === 'am' ? 'ደንብ' : 'Regulation')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(app()->getLocale() === 'am' ? 'ንቁ' : 'Active')
                    ->boolean(),
            ])
            ->defaultSort('code')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
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
