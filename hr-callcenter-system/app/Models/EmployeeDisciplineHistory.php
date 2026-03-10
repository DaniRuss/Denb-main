<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDisciplineHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'discipline_date',
        'discipline_type',
        'description',
        'action_taken',
        'duration_days',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'discipline_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

