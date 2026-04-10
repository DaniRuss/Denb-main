<?php

namespace App\Filament\Resources\ViolationRecordResource\Pages;

use App\Filament\Resources\ViolationRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateViolationRecord extends CreateRecord
{
    protected static string $resource = ViolationRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-calculate repeat offense count
        if (isset($data['violator_id'])) {
            $data['repeat_offense_count'] = \App\Models\ViolationRecord::where('violator_id', $data['violator_id'])->count();
        }

        return $data;
    }
}
