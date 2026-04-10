<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfiscatedAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violation_record_id',
        'description',
        'quantity',
        'unit',
        'is_perishable',
        'seized_date',
        'seizure_receipt_number',
        'seized_by',
        'handover_date',
        'received_by',
        'estimated_value',
        'estimation_date',
        'transfer_deadline',
        'transferred_date',
        'transferred_to_sub_city_id',
        'sold_amount',
        'sold_date',
        'disposed_date',
        'disposal_reason',
        'authority_share',
        'city_finance_share',
        'status',
        'notes',
    ];

    protected $casts = [
        'seized_date' => 'date',
        'handover_date' => 'date',
        'estimation_date' => 'date',
        'transfer_deadline' => 'date',
        'transferred_date' => 'date',
        'sold_date' => 'date',
        'disposed_date' => 'date',
        'estimated_value' => 'decimal:2',
        'sold_amount' => 'decimal:2',
        'authority_share' => 'decimal:2',
        'city_finance_share' => 'decimal:2',
        'is_perishable' => 'boolean',
        'quantity' => 'integer',
    ];

    // ── Relationships ──────────────────────────────

    public function violationRecord(): BelongsTo
    {
        return $this->belongsTo(ViolationRecord::class);
    }

    public function seizedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seized_by');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function transferredToSubCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class, 'transferred_to_sub_city_id');
    }

    // ── Scopes ─────────────────────────────────────

    public function scopeSeized($query)
    {
        return $query->where('status', 'seized');
    }

    public function scopeAwaitingTransfer($query)
    {
        return $query->whereIn('status', ['handed_over', 'estimated'])
            ->where('is_perishable', false);
    }

    public function scopeOverdueTransfer($query)
    {
        return $query->whereNotNull('transfer_deadline')
            ->whereNull('transferred_date')
            ->where('transfer_deadline', '<', now()->toDateString());
    }

    public function scopePerishable($query)
    {
        return $query->where('is_perishable', true);
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    // ── Business Logic ────────────���────────────────

    public function markHandedOver(int $receivedBy): void
    {
        $this->update([
            'status' => 'handed_over',
            'handover_date' => now()->toDateString(),
            'received_by' => $receivedBy,
            'transfer_deadline' => $this->is_perishable ? null : now()->addDays(3)->toDateString(),
        ]);
    }

    public function recordEstimation(float $value): void
    {
        $this->update([
            'status' => 'estimated',
            'estimated_value' => $value,
            'estimation_date' => now()->toDateString(),
        ]);
    }

    public function markTransferred(int $subCityId): void
    {
        $this->update([
            'status' => 'transferred',
            'transferred_date' => now()->toDateString(),
            'transferred_to_sub_city_id' => $subCityId,
        ]);
    }

    public function recordSale(float $amount): void
    {
        $authorityShare = round($amount * 0.60, 2);
        $cityShare = round($amount * 0.40, 2);

        $this->update([
            'status' => 'sold',
            'sold_amount' => $amount,
            'sold_date' => now()->toDateString(),
            'authority_share' => $authorityShare,
            'city_finance_share' => $cityShare,
        ]);
    }

    public function recordDisposal(string $reason): void
    {
        $this->update([
            'status' => 'disposed',
            'disposed_date' => now()->toDateString(),
            'disposal_reason' => $reason,
        ]);
    }

    public function isTransferOverdue(): bool
    {
        return $this->transfer_deadline
            && !$this->transferred_date
            && $this->transfer_deadline->lt(now());
    }
}
