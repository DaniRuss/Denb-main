<?php

namespace App\Observers;

use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use Illuminate\Support\Carbon;

class ShiftSwapObserver
{
    /**
     * When a swap is approved, reassign the shift to the new employee and update related attendance.
     * Prevents reverting an already approved or rejected swap.
     */
    public function updated(ShiftSwap $shiftSwap): void
    {
        $originalStatus = $shiftSwap->getOriginal('status');
        if (in_array($originalStatus, ['approved', 'rejected']) && $shiftSwap->status !== $originalStatus) {
            $v = \Illuminate\Support\Facades\Validator::make([], []);
            $v->errors()->add('status', __('Cannot change status after a swap has been approved or rejected.'));
            throw new \Illuminate\Validation\ValidationException($v);
        }

        if ($shiftSwap->status !== 'approved') {
            return;
        }

        if (! $shiftSwap->wasChanged('status') || $originalStatus === 'approved') {
            return;
        }

        $assignment = $shiftSwap->shiftAssignment;
        if (! $assignment || $assignment->employee_id != $shiftSwap->employee_from) {
            return;
        }

        $employeeTo = $shiftSwap->employee_to;
        $assignedDate = Carbon::parse($assignment->assigned_date);

        $conflict = ShiftAssignment::query()
            ->where('employee_id', $employeeTo)
            ->whereDate('assigned_date', $assignedDate)
            ->where('id', '!=', $assignment->id)
            ->exists();

        if ($conflict) {
            $v = \Illuminate\Support\Facades\Validator::make([], []);
            $v->errors()->add('status', __('The receiving employee already has a shift assigned on this date.'));
            throw new \Illuminate\Validation\ValidationException($v);
        }

        $assignment->employee_id = $employeeTo;
        $assignment->save();

        $assignment->attendances()
            ->where('employee_id', $shiftSwap->employee_from)
            ->update(['employee_id' => $employeeTo]);
    }
}
