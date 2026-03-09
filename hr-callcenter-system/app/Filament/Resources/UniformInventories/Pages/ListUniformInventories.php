<?php
namespace App\Filament\Resources\UniformInventories\Pages;
use App\Filament\Resources\UniformInventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListUniformInventories extends ListRecords {
    protected static string $resource = UniformInventoryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
