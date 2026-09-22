<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (LeaveRequest $leaveRequest): void {
            if ($leaveRequest->start_date && $leaveRequest->end_date) {
                $startDate = Carbon::parse($leaveRequest->start_date)->toDateString();
                $endDate = Carbon::parse($leaveRequest->end_date)->toDateString();

                if ($endDate < $startDate) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'end_date' => 'End date must be on or after the start date.',
                    ]);
                }
            }
        });

        static::saved(function (LeaveRequest $leaveRequest): void {
            $leaveRequest->syncEmployeeLeaveStatus();
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApprovedOn($query, mixed $date)
    {
        $date = Carbon::parse($date)->toDateString();

        return $query
            ->where('status', self::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function getDateRangeAttribute(): string
    {
        return Carbon::parse($this->start_date)->toDateString() . ' - ' . Carbon::parse($this->end_date)->toDateString();
    }

    public function syncEmployeeLeaveStatus(): void
    {
        $employee = $this->employee;

        if (! $employee) {
            return;
        }

        $isOnLeaveToday = $employee->leaveRequests()
            ->approvedOn(now('Africa/Addis_Ababa'))
            ->exists();

        if ($isOnLeaveToday && $employee->status === 'active') {
            $employee->updateQuietly(['status' => 'on_leave']);
        }

        if (! $isOnLeaveToday && $employee->status === 'on_leave') {
            $employee->updateQuietly(['status' => 'active']);
        }
    }
}
