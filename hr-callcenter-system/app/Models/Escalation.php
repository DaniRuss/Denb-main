<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'caseable_type',
        'caseable_id',
        'complaint_id',
        'escalated_by',
        'escalated_to',
        'from_level',
        'to_level',
        'reason',
        'reason_details',
        'notes',
        'escalated_at',
        'responded_at',
        'status',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function caseable()
    {
        return $this->morphTo();
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function escalatedBy()
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
