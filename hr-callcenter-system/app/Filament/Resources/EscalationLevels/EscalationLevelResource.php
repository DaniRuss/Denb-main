<?php

namespace App\Filament\Resources\EscalationLevels;

use App\Filament\Resources\EscalationLevels\Pages\CreateEscalationLevel;
use App\Filament\Resources\EscalationLevels\Pages\EditEscalationLevel;
use App\Filament\Resources\EscalationLevels\Pages\ListEscalationLevels;
use App\Filament\Resources\EscalationLevels\Schemas\EscalationLevelForm;
use App\Filament\Resources\EscalationLevels\Tables\EscalationLevelsTable;
use App\Models\EscalationLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EscalationLevelResource extends Resource
{
    protected static ?string $model = EscalationLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EscalationLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EscalationLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEscalationLevels::route('/'),
            'create' => CreateEscalationLevel::route('/create'),
            'edit' => EditEscalationLevel::route('/{record}/edit'),
        ];
    }
}
