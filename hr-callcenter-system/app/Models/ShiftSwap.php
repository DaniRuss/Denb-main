<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSwap extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_from',
        'employee_to',
        'shift_assignment_id',
        'approved_by',
        'status',
        'reason',
    ];

    public function employeeFrom()
    {
        return $this->belongsTo(Employee::class, 'employee_from');
    }

    public function employeeTo()
    {
        return $this->belongsTo(Employee::class, 'employee_to');
    }

    public function shiftAssignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
