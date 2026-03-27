<?php
namespace App\Filament\Resources\Escalations\Pages;
use App\Filament\Resources\EscalationResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
class EditEscalation extends EditRecord {
    protected static string $resource = EscalationResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
