<?php
namespace App\Filament\Resources\Officers\Pages;
use App\Filament\Resources\OfficerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
class ViewOfficer extends ViewRecord {
    protected static string $resource = OfficerResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
