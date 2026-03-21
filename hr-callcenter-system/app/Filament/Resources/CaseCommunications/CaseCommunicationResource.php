<?php

namespace App\Filament\Resources\CaseCommunications;

use App\Filament\Resources\CaseCommunications\Pages\CreateCaseCommunication;
use App\Filament\Resources\CaseCommunications\Pages\EditCaseCommunication;
use App\Filament\Resources\CaseCommunications\Pages\ListCaseCommunications;
use App\Filament\Resources\CaseCommunications\Schemas\CaseCommunicationForm;
use App\Filament\Resources\CaseCommunications\Tables\CaseCommunicationsTable;
use App\Models\CaseCommunication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CaseCommunicationResource extends Resource
{
    protected static ?string $model = CaseCommunication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CaseCommunicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CaseCommunicationsTable::configure($table);
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
            'index' => ListCaseCommunications::route('/'),
            'create' => CreateCaseCommunication::route('/create'),
            'edit' => EditCaseCommunication::route('/{record}/edit'),
        ];
    }
}
