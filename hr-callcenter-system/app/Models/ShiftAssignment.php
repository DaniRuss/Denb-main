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
        'zone',
        'assigned_date',
        'assigned_by',
        'status',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

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

        $date = Carbon::parse($this->assigned_date->format('Y-m-d'));
        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return $at->between($start, $end);
    }
}
