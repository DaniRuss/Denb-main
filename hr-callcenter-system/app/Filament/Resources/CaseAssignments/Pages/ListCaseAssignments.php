<?php
namespace App\Filament\Resources\CaseAssignments\Pages;
use App\Filament\Resources\CaseAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListCaseAssignments extends ListRecords {
    protected static string $resource = CaseAssignmentResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
