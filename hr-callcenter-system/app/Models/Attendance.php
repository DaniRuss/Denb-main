<?php

namespace App\Models;

use App\Support\EthiopianTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_assignment_id',
        'attendance_date',
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
        'attendance_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'verified_at' => 'datetime',
        'status_locked' => 'boolean',
        'auto_generated' => 'boolean',
    ];

    // Constants for better maintainability
    const STATUS_PENDING = 'pending';

    const STATUS_PRESENT = 'present';

    const STATUS_ABSENT = 'absent';

    const STATUS_LATE = 'late';

    const STATUS_HALF_DAY = 'half_day';

    const STATUS_OVERTIME = 'overtime';

    const GRACE_MINUTES = 10;

    const HALF_DAY_THRESHOLD_HOURS = 4; // Consider half day if work time is less than this

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
            // Some databases include a required attendances.attendance_date column.
            // Keep compatibility by auto-filling it when present.
            if (! static::hasAttendanceDateColumn()) {
                return;
            }

            if ($attendance->getAttribute('attendance_date')) {
                return;
            }

            $reference = $attendance->check_in
                ?? $attendance->check_out
                ?? now();

            $attendance->setAttribute(
                'attendance_date',
                static::asCarbon($reference)->toDateString()
            );
        });

        static::saving(function (Attendance $attendance): void {
            // Do not recalculate status once locked
            if ($attendance->status_locked) {
                return;
            }

            $attendance->calculateAttendanceStatus();
        });

        static::saving(function (Attendance $attendance): void {
            // Validate check-out is after check-in
            if ($attendance->check_in && $attendance->check_out) {
                if ($attendance->check_out->lessThanOrEqualTo($attendance->check_in)) {
                    throw ValidationException::withMessages([
                        'check_out' => 'Check-out time must be after check-in time.',
                    ]);
                }
            }
        });
    }

    /**
     * Calculate attendance status based on check-in/out times
     */
    public function calculateAttendanceStatus(): void
    {
        $assignment = $this->shiftAssignment;
        $shift = $assignment?->shift;

        $checkIn = $this->normalizeDateTime($this->check_in);
        $checkOut = $this->normalizeDateTime($this->check_out);

        if (! $assignment || ! $shift) {
            $this->attendance_status = $this->attendance_status ?: self::STATUS_PENDING;

            return;
        }

        // Calculate shift boundaries with timezone consideration
        $shiftBoundaries = $this->calculateShiftBoundaries($assignment, $shift);

        // No check-in at all
        if (! $checkIn) {
            $this->attendance_status = self::STATUS_ABSENT;

            return;
        }

        // Determine base status
        $status = $this->determineBaseStatus($checkIn, $shiftBoundaries);

        // Check if employee checked out early
        if ($checkOut && $this->isEarlyCheckout($checkOut, $shiftBoundaries['end'])) {
            $status = self::STATUS_HALF_DAY;
        }

        // Check for overtime
        if ($checkOut && $this->isOvertime($checkOut, $shiftBoundaries['end'])) {
            $status = self::STATUS_OVERTIME;
        }

        $this->attendance_status = $status;
    }

    /**
     * Calculate shift boundaries handling midnight crossovers
     */
    private function calculateShiftBoundaries($assignment, $shift): array
    {
        $reference = $this->normalizeDateTime($this->check_in)
            ?? $this->normalizeDateTime($this->check_out)
            ?? now();

        $reference = Carbon::parse($reference)->timezone('Africa/Addis_Ababa');

        $window = $assignment->shiftWindowForInstant($reference);
        if ($window) {
            return [
                'start' => $window['start'],
                'end' => $window['end'],
                'grace_end' => $window['start']->copy()->addMinutes(self::GRACE_MINUTES),
            ];
        }

        $candidateDate = $reference->copy()->startOfDay();
        $candidateStr = $candidateDate->format('Y-m-d');
        $assignedStartStr = Carbon::parse($assignment->assigned_date)->format('Y-m-d');
        $assignedEndStr = Carbon::parse($assignment->end_date)->format('Y-m-d');

        if ($candidateStr < $assignedStartStr) {
            $candidateDate = Carbon::createFromFormat('Y-m-d', $assignedStartStr, 'Africa/Addis_Ababa')->startOfDay();
        } elseif ($candidateStr > $assignedEndStr) {
            $candidateDate = Carbon::createFromFormat('Y-m-d', $assignedEndStr, 'Africa/Addis_Ababa')->startOfDay();
        }

        [$shiftStart, $shiftEnd] = EthiopianTime::shiftWindowOnLocalDate($shift, $candidateDate);

        return [
            'start' => $shiftStart,
            'end' => $shiftEnd,
            'grace_end' => $shiftStart->copy()->addMinutes(self::GRACE_MINUTES),
        ];
    }

    /**
     * Determine base attendance status (present, late)
     */
    private function determineBaseStatus($checkIn, array $boundaries): string
    {
        if ($checkIn->lessThanOrEqualTo($boundaries['grace_end'])) {
            return self::STATUS_PRESENT;
        }

        return self::STATUS_LATE;
    }

    /**
     * Check if employee checked out early
     */
    private function isEarlyCheckout($checkOut, $shiftEnd): bool
    {
        // Calculate worked hours
        $workedHours = $checkOut->diffInHours($this->check_in);

        // Check if checkout is significantly before shift end or worked hours are less than threshold
        return $checkOut->lessThan($shiftEnd->copy()->subHours(self::HALF_DAY_THRESHOLD_HOURS))
            || $workedHours < self::HALF_DAY_THRESHOLD_HOURS;
    }

    /**
     * Check if employee worked overtime
     */
    private function isOvertime($checkOut, $shiftEnd): bool
    {
        return $checkOut->greaterThan($shiftEnd);
    }

    /**
     * Normalize datetime fields
     */
    private function normalizeDateTime($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value) {
            return Carbon::parse($value);
        }

        return null;
    }

    private static function asCarbon(mixed $value): Carbon
    {
        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    private static function hasAttendanceDateColumn(): bool
    {
        static $hasColumn;

        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $hasColumn = Schema::hasColumn((new static)->getTable(), 'attendance_date');

        return $hasColumn;
    }

    /**
     * Helper method to get formatted work duration
     */
    public function getWorkDurationAttribute(): ?string
    {
        if (! $this->check_in || ! $this->check_out) {
            return null;
        }

        $duration = $this->check_out->diff($this->check_in);

        return $duration->format('%H:%I:%S');
    }

    /**
     * Scope for attendance by date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for attendance by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('attendance_status', $status);
    }

    /**
     * Scope for verified attendance
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }
}
