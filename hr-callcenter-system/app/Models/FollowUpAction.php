<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUpAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'incident_report_id',
        'action_type_id',
        'due_date',
        'completed_at',
        'status',
        'notes',
        'assigned_by',
        'assigned_to',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class);
    }

    public function actionType()
    {
        return $this->belongsTo(ActionType::class);
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

