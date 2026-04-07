<?php

namespace App\Filament\Resources;

use App\Models\PenaltyType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PenaltyTypeResource extends Resource
{
    protected static ?string $model = PenaltyType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት እና እርምጃ' : 'Penalty & Action';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የቅጣት አይነቶች' : 'Penalty Types';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Penalty Type')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('default_duration_days')
                        ->label('Default Duration (Days)')
                        ->numeric()
                        ->minValue(1),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
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
                Tables\Columns\TextColumn::make('default_duration_days')->label('Default Days')->sortable(),
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
            'index' => \App\Filament\Resources\PenaltyTypeResource\Pages\ListPenaltyTypes::route('/'),
            'create' => \App\Filament\Resources\PenaltyTypeResource\Pages\CreatePenaltyType::route('/create'),
            'edit' => \App\Filament\Resources\PenaltyTypeResource\Pages\EditPenaltyType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }
}
