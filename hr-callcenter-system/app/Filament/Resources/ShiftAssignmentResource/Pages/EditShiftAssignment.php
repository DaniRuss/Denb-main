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
            $geo = ShiftAssignmentResource::resolveSupervisorGeography($user);

            if (! $geo) {
                abort(403);
            }

            $guard = (string) config('auth.defaults.guard', 'web');

            $query->whereHas('employee', function ($q) use ($geo, $guard) {
                $q->where('status', 'active')
                    ->when($geo['exclude_employee_id'], fn ($sq, $id) => $sq->where('id', '!=', $id))
                    ->when($geo['sub_city_id'], fn ($sq, $id) => $sq->where('sub_city_id', $id))
                    ->when($geo['woreda_id'], fn ($sq, $id) => $sq->where('woreda_id', $id))
                    ->where(function ($eq) use ($guard) {
                        $eq->whereNull('user_id')
                            ->orWhereHas('user', function ($uq) use ($guard) {
                                $uq->whereDoesntHave('roles', function ($rq) use ($guard) {
                                    $rq->whereIn('name', ['admin', 'supervisor'])
                                        ->where('guard_name', $guard);
                                });
                            });
                    });
            });
        }

        return $query->findOrFail($key);
    }
}
