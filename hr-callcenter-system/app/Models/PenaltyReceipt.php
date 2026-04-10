<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenaltyReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violation_record_id',
        'receipt_number',
        'issued_date',
        'issued_time',
        'fine_amount',
        'paid_amount',
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
        return $query->where('payment_status', 'pending')
            ->where('payment_deadline', '<', now()->toDateString());
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
        return $this->payment_status === 'pending'
            && $this->payment_deadline->lt(now());
    }

    public function markAsPaid(string $paidDate = null): void
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_date' => $paidDate ?? now()->toDateString(),
            'paid_amount' => $this->is_court_case ? $this->court_fine_amount : $this->fine_amount,
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
