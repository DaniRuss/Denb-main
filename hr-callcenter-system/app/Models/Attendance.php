<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_assignment_id',
        'check_in',
        'check_out',
        'attendance_status',
        'status_locked',
        'verified_by',
        'verified_at',
        'auto_generated',
        'check_in_location',
        'check_out_location',
        'remarks',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'verified_at' => 'datetime',
        'status_locked' => 'boolean',
        'auto_generated' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftAssignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Attendance $attendance): void {
            // Do not recalculate status once locked.
            if ($attendance->status_locked) {
                return;
            }

            $status = 'pending';

            $assignment = $attendance->shiftAssignment;
            $shift = $assignment?->shift;

            $checkIn = $attendance->check_in instanceof Carbon
                ? $attendance->check_in
                : ($attendance->check_in ? Carbon::parse($attendance->check_in) : null);

            $checkOut = $attendance->check_out instanceof Carbon
                ? $attendance->check_out
                : ($attendance->check_out ? Carbon::parse($attendance->check_out) : null);

            if (! $assignment || ! $shift) {
                // Fallback: keep whatever is already set.
                $attendance->attendance_status = $attendance->attendance_status ?: 'pending';
                return;
            }

            // Build shift start & end as full DateTimes on assigned date.
            $assignedDate = Carbon::parse($assignment->assigned_date);
            $shiftStart = Carbon::parse($assignedDate->format('Y-m-d') . ' ' . $shift->start_time);
            $shiftEnd = Carbon::parse($assignedDate->format('Y-m-d') . ' ' . $shift->end_time);

            // 10 minute grace period.
            $graceEnd = $shiftStart->copy()->addMinutes(10);

            if (! $checkIn) {
                // No check-in at all.
                $status = 'absent';
            } else {
                if ($checkIn->lessThanOrEqualTo($graceEnd)) {
                    $status = 'present';
                } else {
                    $status = 'late';
                }

                // Early leave -> half day (if checked out significantly before scheduled end).
                if ($checkOut && $checkOut->lessThan($shiftEnd)) {
                    $status = 'half_day';
                }
            }

            $attendance->attendance_status = $status;
        });
    }
}
