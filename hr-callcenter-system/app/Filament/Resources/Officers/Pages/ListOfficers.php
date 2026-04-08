<?php
namespace App\Filament\Resources\Officers\Pages;
use App\Filament\Resources\OfficerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListOfficers extends ListRecords {
    protected static string $resource = OfficerResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
