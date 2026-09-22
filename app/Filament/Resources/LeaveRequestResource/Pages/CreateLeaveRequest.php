<?php

namespace App\Filament\Resources\LeaveRequestResource\Pages;

use App\Filament\Resources\LeaveRequestResource;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();

        if ($user && ! $user->hasAnyRole(['admin', 'supervisor'])) {
            $data['employee_id'] = Employee::query()->where('user_id', $user->id)->value('id');
            $data['status'] = LeaveRequest::STATUS_PENDING;
        }

        return LeaveRequest::create($data);
    }
}
