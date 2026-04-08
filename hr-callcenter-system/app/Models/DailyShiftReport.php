<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyShiftReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_assignment_id',
        'report_text',
        'incident_count',
        'penalty_count',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftAssignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }
}