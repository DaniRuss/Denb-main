<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenaltyScheduleResource\Pages;
use App\Filament\Resources\PenaltyScheduleResource\RelationManagers\ViolationTypesRelationManager;
use App\Models\PenaltySchedule;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PenaltyScheduleResource extends Resource
{
    protected static ?string $model = PenaltySchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት እና እርምጃ' : 'Penalty & Action';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት ሰንጠረዥ' : 'Penalty Schedules';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት ሰንጠረዥ' : 'Penalty Schedule';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት ሰንጠረዦች' : 'Penalty Schedules';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(app()->getLocale() === 'am' ? 'የቅጣት ሰንጠረዥ መረጃ' : 'Schedule Information')
                ->schema([
                    Forms\Components\TextInput::make('name_am')
                        ->label(app()->getLocale() === 'am' ? 'ስም (አማርኛ)' : 'Name (Amharic)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_en')
                        ->label(app()->getLocale() === 'am' ? 'ስም (እንግሊዝኛ)' : 'Name (English)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('level')
                        ->label(app()->getLocale() === 'am' ? 'ደረጃ' : 'Level')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(10)
                        ->required()
                        ->unique(ignoreRecord: true),
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
                Tables\Columns\TextColumn::make('level')
                    ->label(app()->getLocale() === 'am' ? 'ደረጃ' : 'Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_am')
                    ->label(app()->getLocale() === 'am' ? 'ስም' : 'Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('violation_types_count')
                    ->label(app()->getLocale() === 'am' ? 'የጥፋት አይነቶች' : 'Violation Types')
                    ->counts('violationTypes')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(app()->getLocale() === 'am' ? 'ንቁ' : 'Active')
                    ->boolean(),
            ])
            ->defaultSort('level')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ViolationTypesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenaltySchedules::route('/'),
            'create' => Pages\CreatePenaltySchedule::route('/create'),
            'view' => Pages\ViewPenaltySchedule::route('/{record}'),
            'edit' => Pages\EditPenaltySchedule::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }
}
