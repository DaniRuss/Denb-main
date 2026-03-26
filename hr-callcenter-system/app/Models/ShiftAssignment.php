<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'block',
        'assigned_date',
        'end_date',
        'assigned_by',
        'status',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShiftAssignment $assignment): void {
            if (! $assignment->assigned_date) {
                return;
            }

            // Always derive end_date from assigned_date in Gregorian calendar.
            $start = Carbon::parse($assignment->assigned_date)->startOfDay();
            $assignment->attributes['end_date'] = $start->copy()->addDays(29)->toDateString();
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function shiftSwaps()
    {
        return $this->hasMany(ShiftSwap::class);
    }

    public function dailyShiftReports()
    {
        return $this->hasMany(DailyShiftReport::class);
    }

    /**
     * Check if the given time (default: now) falls within this assignment's shift window.
     */
    public function isWithinShift(?Carbon $at = null): bool
    {
        $at = $at ?? now();
        $shift = $this->shift;
        if (! $shift) {
            return false;
        }

        $assignedStart = Carbon::parse($this->assigned_date)->startOfDay();
        $assignedEnd = Carbon::parse($this->end_date)->startOfDay();

        // Check both same-day and previous-day windows to support overnight shifts.
        foreach ([0, 1] as $dayOffset) {
            $shiftStartDate = $at->copy()->subDays($dayOffset)->startOfDay();

            if ($shiftStartDate->lt($assignedStart) || $shiftStartDate->gt($assignedEnd)) {
                continue;
            }

            $start = Carbon::parse($shiftStartDate->format('Y-m-d') . ' ' . $shift->start_time);
            $end = Carbon::parse($shiftStartDate->format('Y-m-d') . ' ' . $shift->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            if ($at->between($start, $end)) {
                return true;
            }
        }

        return false;
    }
}