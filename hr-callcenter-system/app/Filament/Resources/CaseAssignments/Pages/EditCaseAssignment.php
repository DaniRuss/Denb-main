<?php
namespace App\Filament\Resources\CaseAssignments\Pages;
use App\Filament\Resources\CaseAssignmentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
class EditCaseAssignment extends EditRecord {
    protected static string $resource = CaseAssignmentResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
