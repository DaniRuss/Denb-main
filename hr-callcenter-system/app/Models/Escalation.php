<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'escalated_by',
        'escalated_to',
        'level',
        'reason',
        'notes',
        'resolved_at',
        'status',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

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
