<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenaltyAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'incident_report_id',
        'penalty_type_id',
        'assigned_date',
        'due_date',
        'duration_days',
        'status',
        'notes',
        'assigned_by',
        'assigned_to',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
    ];

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class);
    }

    public function penaltyType()
    {
        return $this->belongsTo(PenaltyType::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

