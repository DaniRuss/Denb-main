<?php

namespace App\Filament\Resources;

use App\Models\ActionType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ActionTypeResource extends Resource
{
    protected static ?string $model = ActionType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Penalty & Action';

    protected static ?string $navigationLabel = 'Action Types';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Action Type')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(5000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ActionTypeResource\Pages\ListActionTypes::route('/'),
            'create' => \App\Filament\Resources\ActionTypeResource\Pages\CreateActionType::route('/create'),
            'edit' => \App\Filament\Resources\ActionTypeResource\Pages\EditActionType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }
}

