<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViolationRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violator_id',
        'violation_type_id',
        'sub_city_id',
        'woreda_id',
        'block',
        'specific_location',
        'violation_date',
        'violation_time',
        'regulation_number',
        'article',
        'sub_article',
        'fine_amount',
        'repeat_offense_count',
        'action_taken',
        'status',
        'investigation_notes',
        'reported_by',
        'verified_by',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'fine_amount' => 'decimal:2',
        'repeat_offense_count' => 'integer',
    ];

    // ── Relationships ──────────────────────────────

    public function violator(): BelongsTo
    {
        return $this->belongsTo(Violator::class);
    }

    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class);
    }

    public function subCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class);
    }

    public function reportedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function penaltyReceipts(): HasMany
    {
        return $this->hasMany(PenaltyReceipt::class);
    }

    public function warningLetters(): HasMany
    {
        return $this->hasMany(WarningLetter::class);
    }

    public function confiscatedAssets(): HasMany
    {
        return $this->hasMany(ConfiscatedAsset::class);
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'payment_pending')
            ->whereHas('penaltyReceipts', fn ($q) => $q->where('payment_deadline', '<', now()));
    }

    public function scopeInBlock($query, string $block)
    {
        return $query->where('block', $block);
    }

    // ── Helpers ─────────────────────────────────────

    public function getLegalReferenceAttribute(): string
    {
        $parts = array_filter([
            $this->regulation_number ? "ደንብ {$this->regulation_number}" : null,
            $this->article ? "አንቀጽ {$this->article}" : null,
            $this->sub_article ? "ንዑስ አንቀጽ {$this->sub_article}" : null,
        ]);

        return implode(' ', $parts);
    }

    public function calculateRepeatCount(): int
    {
        return self::where('violator_id', $this->violator_id)
            ->where('id', '!=', $this->id ?? 0)
            ->count();
    }
}
