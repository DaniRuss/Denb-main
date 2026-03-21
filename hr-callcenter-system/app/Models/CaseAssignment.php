<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'caseable_type',
        'caseable_id',
        'complaint_id',
        'assigned_by',
        'assigned_to',
        'department_id',
        'assignment_type',
        'assignment_notes',
        'assigned_at',
        'deadline',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function caseable()
    {
        return $this->morphTo();
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
