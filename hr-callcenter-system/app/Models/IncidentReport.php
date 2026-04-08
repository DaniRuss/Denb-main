<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidentReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'incident_type',
        'location',
        'incident_date',
        'description',
        'status',
        'reported_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function penaltyAssignments()
    {
        return $this->hasMany(PenaltyAssignment::class);
    }

    public function followUpActions()
    {
        return $this->hasMany(FollowUpAction::class);
    }
}

