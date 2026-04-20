<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenaltyReceipt extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        $observer = new \App\Observers\ViolationStatusObserver();

        static::saving(function (self $receipt) {
            if ($receipt->payment_status === 'court_filed') {
                $receipt->is_court_case = true;
                if (! $receipt->court_fine_amount) {
                    $receipt->court_fine_amount = (float) $receipt->fine_amount * 2;
                }
                if (! $receipt->court_filed_date) {
                    $receipt->court_filed_date = now()->toDateString();
                }
            }

            if (in_array($receipt->payment_status, ['paid', 'court_paid'], true)) {
                $amount = (float) ($receipt->is_court_case || $receipt->payment_status === 'court_paid'
                    ? ($receipt->court_fine_amount ?? $receipt->fine_amount)
                    : $receipt->fine_amount);

                if (! $receipt->paid_amount || (float) $receipt->paid_amount <= 0) {
                    $receipt->paid_amount = $amount;
                }

                $paid = (float) $receipt->paid_amount;
                $receipt->authority_share    = round($paid * 0.60, 2);
                $receipt->city_finance_share = round($paid * 0.40, 2);
            }
        });

        static::created(fn (self $receipt) => $observer->createdReceipt($receipt));
        static::updated(fn (self $receipt) => $observer->updatedReceipt($receipt));
    }

    protected $fillable = [
        'violation_record_id',
        'receipt_number',
        'issued_date',
        'issued_time',
        'fine_amount',
        'paid_amount',
        'authority_share',
        'city_finance_share',
        'payment_deadline',
        'paid_date',
        'payment_status',
        'is_court_case',
        'court_fine_amount',
        'court_filed_date',
        'receipt_refused',
        'issued_by',
        'witness_officer_1',
        'witness_officer_2',
        'witness_officer_3',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'payment_deadline' => 'date',
        'paid_date' => 'date',
        'court_filed_date' => 'date',
        'fine_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'authority_share' => 'decimal:2',
        'city_finance_share' => 'decimal:2',
        'court_fine_amount' => 'decimal:2',
        'is_court_case' => 'boolean',
        'receipt_refused' => 'boolean',
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

    public function witnessOfficer1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_officer_1');
    }

    public function witnessOfficer2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_officer_2');
    }

    public function witnessOfficer3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_officer_3');
    }

    // ── Scopes ─────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeCourtCases($query)
    {
        return $query->where('is_court_case', true);
    }

    // ── Business Logic ─────────────────────────────

    public function isOverdue(): bool
    {
        return $this->payment_status === 'overdue'
            || ($this->payment_status === 'pending' && $this->payment_deadline?->lt(now()->startOfDay()));
    }

    public function markAsPaid(string $paidDate = null): void
    {
        $amount = (float) ($this->is_court_case ? $this->court_fine_amount : $this->fine_amount);

        $this->update([
            'payment_status'      => $this->is_court_case ? 'court_paid' : 'paid',
            'paid_date'           => $paidDate ?? now()->toDateString(),
            'paid_amount'         => $amount,
            'authority_share'     => round($amount * 0.60, 2),
            'city_finance_share'  => round($amount * 0.40, 2),
        ]);
    }

    public function escalateToCourt(): void
    {
        $this->update([
            'payment_status' => 'court_filed',
            'is_court_case' => true,
            'court_fine_amount' => $this->fine_amount * 2, // double fine per regulation
            'court_filed_date' => now()->toDateString(),
        ]);
    }

    public function getRemainingAmountAttribute(): float
    {
        $total = $this->is_court_case ? $this->court_fine_amount : $this->fine_amount;
        return (float) $total - (float) $this->paid_amount;
    }
}
