<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Escalation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        static::created(function (self $escalation) {
            if ($escalation->escalated_to) {
                $target = User::find($escalation->escalated_to);
                $target?->notify(new \App\Notifications\CaseEscalatedNotification($escalation));
            }
        });
    }

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
        'level' => 'integer',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function escalatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }
}
