<?php

namespace App\Models;

use App\Support\EthiopianDate;
use App\Support\EthiopianTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

            // Match Filament sync: calendar date in Africa/Addis_Ababa, +29 days = 30-day inclusive window.
            $dateOnly = Carbon::parse($assignment->assigned_date)->format('Y-m-d');
            $start = Carbon::createFromFormat('Y-m-d', $dateOnly, 'Africa/Addis_Ababa')->startOfDay();
            $assignment->attributes['end_date'] = $start->copy()->addDays(29)->format('Y-m-d');
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

    /**
     * Today's attendance row for this assignment (Addis Ababa calendar date).
     */
    public function todayAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'shift_assignment_id')
            ->whereDate('attendance_date', EthiopianDate::todayGregorianInAddisAbaba());
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
     * Shift window [start, end] in Africa/Addis_Ababa that contains $at, if any.
     * Checks today and yesterday to support overnight shifts.
     *
     * @return array{start: Carbon, end: Carbon}|null
     */
    public function shiftWindowForInstant(?Carbon $at = null): ?array
    {
        $at = Carbon::parse($at ?? now())->timezone('Africa/Addis_Ababa');
        $shift = $this->shift;
        if (! $shift) {
            return null;
        }

        // Compare calendar dates only (Y-m-d) so assignment bounds match “today” in Addis Ababa.
        // Mixing UTC midnight from date casts with Addis start-of-day broke isWithinShift during the shift.
        $assignedStartStr = Carbon::parse($this->assigned_date)->format('Y-m-d');
        $assignedEndStr = Carbon::parse($this->end_date)->format('Y-m-d');

        foreach ([0, 1] as $dayOffset) {
            $shiftStartDate = $at->copy()->subDays($dayOffset)->startOfDay();
            $shiftDateStr = $shiftStartDate->format('Y-m-d');

            if ($shiftDateStr < $assignedStartStr || $shiftDateStr > $assignedEndStr) {
                continue;
            }

            [$start, $end] = EthiopianTime::shiftWindowOnLocalDate($shift, $shiftStartDate);

            if ($at->greaterThanOrEqualTo($start) && $at->lessThanOrEqualTo($end)) {
                return ['start' => $start, 'end' => $end];
            }
        }

        return null;
    }

    /**
     * Check if the given time (default: now) falls within this assignment's shift window.
     */
    public function isWithinShift(?Carbon $at = null): bool
    {
        return $this->shiftWindowForInstant($at) !== null;
    }
}
