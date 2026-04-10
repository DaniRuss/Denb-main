<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarningLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violation_record_id',
        'reference_number',
        'warning_type',
        'issued_date',
        'deadline',
        'complied',
        'complied_at',
        'regulation_number',
        'article',
        'sub_article',
        'delivery_method',
        'violator_accepted',
        'escalated_to_task_force',
        'escalation_date',
        'issued_by',
        'issued_by_officer_2',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'deadline' => 'datetime',
        'complied_at' => 'datetime',
        'escalation_date' => 'date',
        'complied' => 'boolean',
        'violator_accepted' => 'boolean',
        'escalated_to_task_force' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function violationRecord(): BelongsTo
    {
        return $this->belongsTo(ViolationRecord::class);
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function issuedByOfficer2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_officer_2');
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeThreeDay($query)
    {
        return $query->where('warning_type', 'three_day');
    }

    public function scopeTwentyFourHour($query)
    {
        return $query->where('warning_type', 'twenty_four_hour');
    }

    public function scopeExpired($query)
    {
        return $query->where('complied', false)
            ->where('deadline', '<', now());
    }

    public function scopePendingCompliance($query)
    {
        return $query->where('complied', false)
            ->where('deadline', '>=', now());
    }

    // ── Business Logic ─────────────────────────────

    public function isExpired(): bool
    {
        return !$this->complied && $this->deadline->lt(now());
    }

    public function markComplied(): void
    {
        $this->update([
            'complied' => true,
            'complied_at' => now(),
        ]);
    }

    public function escalateToTaskForce(): void
    {
        $this->update([
            'escalated_to_task_force' => true,
            'escalation_date' => now()->toDateString(),
        ]);
    }

    public function getLegalReferenceAttribute(): string
    {
        $parts = array_filter([
            $this->regulation_number ? "ደንብ {$this->regulation_number}" : null,
            $this->article ? "አንቀጽ {$this->article}" : null,
            $this->sub_article ? "ንዑስ አንቀጽ {$this->sub_article}" : null,
        ]);

        return implode(' ', $parts);
    }
}
