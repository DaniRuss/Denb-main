<?php
namespace App\Filament\Resources\Escalations\Pages;
use App\Filament\Resources\EscalationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListEscalations extends ListRecords {
    protected static string $resource = EscalationResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
