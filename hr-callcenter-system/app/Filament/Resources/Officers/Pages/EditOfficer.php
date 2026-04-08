<?php
namespace App\Filament\Resources\Officers\Pages;
use App\Filament\Resources\OfficerResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
class EditOfficer extends EditRecord {
    protected static string $resource = OfficerResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
