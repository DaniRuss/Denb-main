<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof LeaveRequest) {
            return parent::handleRecordUpdate($record, $data);
        }

        $user = Auth::user();

        if ($user && ! $user->hasAnyRole(['admin', 'supervisor'])) {
            unset($data['status'], $data['reviewed_by'], $data['reviewed_at'], $data['review_note']);
        }

        if (($data['status'] ?? null) && $data['status'] !== $record->status && $user?->hasAnyRole(['admin', 'supervisor'])) {
            $data['reviewed_by'] = Auth::id();
            $data['reviewed_at'] = now();
        }

        $record->update($data);

        return $record;
    }
}
