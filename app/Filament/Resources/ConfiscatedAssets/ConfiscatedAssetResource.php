<?php

namespace App\Filament\Resources\ConfiscatedAssets;

use App\Filament\Resources\ConfiscatedAssets\Pages\CreateConfiscatedAsset;
use App\Filament\Resources\ConfiscatedAssets\Pages\EditConfiscatedAsset;
use App\Filament\Resources\ConfiscatedAssets\Pages\ListConfiscatedAssets;
use App\Filament\Resources\ConfiscatedAssets\Pages\ViewConfiscatedAsset;
use App\Filament\Resources\ConfiscatedAssets\Schemas\ConfiscatedAssetForm;
use App\Filament\Resources\ConfiscatedAssets\Schemas\ConfiscatedAssetInfolist;
use App\Filament\Resources\ConfiscatedAssets\Tables\ConfiscatedAssetsTable;
use App\Models\ConfiscatedAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConfiscatedAssetResource extends Resource
{
    protected static ?string $model = ConfiscatedAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'item_description';

    public static function form(Schema $schema): Schema
    {
        return ConfiscatedAssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConfiscatedAssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConfiscatedAssetsTable::configure($table);
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
            'index' => ListConfiscatedAssets::route('/'),
            'create' => CreateConfiscatedAsset::route('/create'),
            'view' => ViewConfiscatedAsset::route('/{record}'),
            'edit' => EditConfiscatedAsset::route('/{record}/edit'),
        ];
    }
}
