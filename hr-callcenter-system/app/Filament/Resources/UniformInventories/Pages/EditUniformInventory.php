<?php
namespace App\Filament\Resources\UniformInventories\Pages;
use App\Filament\Resources\UniformInventoryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
class EditUniformInventory extends EditRecord {
    protected static string $resource = UniformInventoryResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
