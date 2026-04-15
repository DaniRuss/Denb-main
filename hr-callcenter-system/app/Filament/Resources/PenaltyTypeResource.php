<?php

namespace App\Filament\Resources;

use App\Models\PenaltyType;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class PenaltyTypeResource extends Resource
{
    protected static ?string $model = PenaltyType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?int $navigationSort = 2;

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
            \Filament\Schemas\Components\Section::make(app()->getLocale() === 'am' ? 'የቅጣት አይነት' : 'Penalty Type')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('default_duration_days')
                        ->label(app()->getLocale() === 'am' ? 'ነባሪ ቆይታ (ቀናት)' : 'Default Duration (Days)')
                        ->numeric()
                        ->minValue(1),
                    Forms\Components\Toggle::make('is_active')
                        ->label(app()->getLocale() === 'am' ? 'ንቁ' : 'Active')
                        ->default(true),
                    Forms\Components\Textarea::make('description')
                        ->label(app()->getLocale() === 'am' ? 'ማብራሪያ' : 'Description')
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
                Tables\Columns\TextColumn::make('name')
                    ->label(app()->getLocale() === 'am' ? 'ስም' : 'Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_duration_days')
                    ->label(app()->getLocale() === 'am' ? 'ነባሪ ቀናት' : 'Default Days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(app()->getLocale() === 'am' ? 'ማብራሪያ' : 'Description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(app()->getLocale() === 'am' ? 'ንቁ' : 'Active'),
            ])
            ->defaultSort('name')
            ->actions([
                Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
                Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')),
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
        return auth()->check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }
}
