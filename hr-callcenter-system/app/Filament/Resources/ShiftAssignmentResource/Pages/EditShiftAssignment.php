<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditShiftAssignment extends EditRecord
{
    protected static string $resource = ShiftAssignmentResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = ShiftAssignment::query();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('officer')) {
            $employee = Employee::query()->where('user_id', $user->id)->first();

            $query->where('employee_id', $employee?->id ?? 0);
        } elseif ($user->hasRole('supervisor')) {
            $supervisor = Employee::query()->where('user_id', $user->id)->first();

            if (! $supervisor) {
                abort(403);
            }

            $query->whereHas('employee', function ($q) use ($supervisor) {
                $q->where('id', '!=', $supervisor->id)
                    ->where('status', 'active')
                    ->when($supervisor->sub_city_id, fn ($sq, $v) => $sq->where('sub_city_id', $v))
                    ->when($supervisor->woreda_id, fn ($sq, $v) => $sq->where('woreda_id', $v))
                    ->whereHas('user', fn ($uq) => $uq->role('officer'));
            });
        }

        return $query->findOrFail($key);
    }
}
