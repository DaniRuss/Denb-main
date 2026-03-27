<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuarterlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'quarter',
        'year',
        'period_start',
        'period_end',
        'total_complaints',
        'resolved_complaints',
        'pending_complaints',
        'total_tips',
        'verified_tips',
        'total_escalations',
        'department_id',
        'prepared_by',
        'approved_by',
        'summary',
        'recommendations',
        'status',
        'report_file',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getResolutionRateAttribute(): ?float
    {
        if ($this->total_complaints == 0)
            return null;
        return round(($this->resolved_complaints / $this->total_complaints) * 100, 1);
    }
}
