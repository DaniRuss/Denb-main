<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\ShiftAssignment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShiftAssignment extends CreateRecord
{
    protected static string $resource = ShiftAssignmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['assigned_by'] = auth()->id();

        return ShiftAssignment::create($data);
    }
}
