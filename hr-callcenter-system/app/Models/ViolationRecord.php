<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class ViolationRecord extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        static::creating(function (self $record) {
            if ($record->violator_id) {
                $record->repeat_offense_count = self::where('violator_id', $record->violator_id)->count();
            }
        });

        // Spec ¶91: "ወዳያዉኑ እርምጃ" — when immediate_penalty is checked, issue the
        // penalty receipt right away without waiting for 3 accumulated warnings.
        static::created(function (self $record) {
            if (! $record->immediate_penalty) {
                return;
            }

            try {
                $receiptNo = 'ቀጥ-' . now()->format('ymdHis') . '-' . str_pad($record->id, 4, '0', STR_PAD_LEFT);

                $record->penaltyReceipts()->create([
                    'receipt_number'   => $receiptNo,
                    'issued_date'      => now()->toDateString(),
                    'issued_time'      => now()->format('H:i'),
                    'fine_amount'      => $record->fine_amount,
                    'payment_deadline' => now()->addDays(3)->toDateString(),
                    'payment_status'   => 'pending',
                    'issued_by'        => $record->reported_by,
                    'notes'            => 'ቀጥተኛ ቅጣት — ማስጠንቀቂያ ሳያስፈልግ',
                ]);
            } catch (\Throwable $e) {
                Log::error('[ImmediatePenalty] auto-receipt creation failed', [
                    'violation_record_id' => $record->id,
                    'error'               => $e->getMessage(),
                ]);
            }
        });
    }

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
        'immediate_penalty',
    ];

    protected $casts = [
        'violation_date'      => 'date',
        'violation_time'      => 'string',
        'fine_amount'         => 'decimal:2',
        'repeat_offense_count'=> 'integer',
        'immediate_penalty'   => 'boolean',
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
            ->whereHas('penaltyReceipts', fn ($q) => $q->where('payment_deadline', '<', now()->startOfDay()));
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
