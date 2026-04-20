<?php

namespace App\Observers;

use App\Models\ConfiscatedAsset;
use App\Models\PenaltyReceipt;
use App\Models\ViolationRecord;
use App\Models\WarningLetter;
use App\Services\Sms\ViolatorNotifier;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ViolationStatusObserver
{
    public function createdReceipt(PenaltyReceipt $receipt): void
    {
        $this->recomputeStatus($receipt->violationRecord);
        $this->notifier()?->penaltyReceiptIssued($receipt);
    }

    public function updatedReceipt(PenaltyReceipt $receipt): void
    {
        if (! $receipt->wasChanged('payment_status')) {
            return;
        }

        $record = $receipt->violationRecord;
        $this->recomputeStatus($record);

        $notifier = $this->notifier();
        if ($notifier && $record) {
            match ($receipt->payment_status) {
                'court_filed'        => $notifier->courtFiled($receipt),
                'overdue'            => $notifier->paymentOverdue($receipt),
                'paid', 'court_paid' => $notifier->complianceThanks($record),
                default              => null,
            };
        }
    }

    public function createdWarning(WarningLetter $letter): void
    {
        $this->recomputeStatus($letter->violationRecord);
        $this->notifier()?->warningIssued($letter);
        $this->autoIssuePenaltyAfterThirdWarning($letter);
    }

    /**
     * Spec ¶90-92: after 3 accumulated warnings for a violator, automatically
     * issue a penalty receipt so the payment + court escalation chain can begin.
     * The receipt creation fires createdReceipt → recomputeStatus + SMS.
     */
    private function autoIssuePenaltyAfterThirdWarning(WarningLetter $letter): void
    {
        $record = $letter->violationRecord;

        if (! $record?->violator_id) {
            return;
        }

        // Count ALL warning letters ever issued to this violator across all their violation records.
        $totalWarnings = WarningLetter::whereHas(
            'violationRecord',
            fn ($q) => $q->where('violator_id', $record->violator_id)
        )->count();

        if ($totalWarnings < 3) {
            return;
        }

        // Skip if an active (not yet paid/closed) penalty receipt already exists for this record.
        $hasActivePenalty = $record->penaltyReceipts()
            ->whereNotIn('payment_status', ['paid', 'court_paid'])
            ->exists();

        if ($hasActivePenalty) {
            return;
        }

        try {
            $receiptNo = 'W3-' . now()->format('ymdHis') . '-' . str_pad($record->id, 4, '0', STR_PAD_LEFT);

            $record->penaltyReceipts()->create([
                'receipt_number'   => $receiptNo,
                'issued_date'      => now()->toDateString(),
                'issued_time'      => now()->format('H:i'),
                'fine_amount'      => $record->fine_amount,
                'payment_deadline' => now()->addDays(3)->toDateString(),
                'payment_status'   => 'pending',
                'issued_by'        => $letter->issued_by,
                'notes'            => "ቅጣት ራስ-ሰር ተፈጠረ — {$totalWarnings}ኛ ማስጠንቀቂያ ደርሷል",
            ]);
        } catch (\Throwable $e) {
            Log::error('[AutoPenalty] failed to auto-create receipt after 3rd warning', [
                'violation_record_id' => $record->id,
                'warning_count'       => $totalWarnings,
                'error'               => $e->getMessage(),
            ]);
        }
    }

    public function updatedWarning(WarningLetter $letter): void
    {
        if (! $letter->wasChanged('complied')) {
            return;
        }

        $this->recomputeStatus($letter->violationRecord);

        if ($letter->complied && $letter->violationRecord) {
            $this->notifier()?->complianceThanks($letter->violationRecord);
        }
    }

    public function createdAsset(ConfiscatedAsset $asset): void
    {
        $this->recomputeStatus($asset->violationRecord);
    }

    /**
     * Derive ViolationRecord.status from the current state of all children.
     *
     * Precedence (highest first):
     *   closed         — every receipt paid & every warning complied
     *   paid           — no pending/overdue work; at least one receipt paid
     *   court_filed    — any receipt in court_filed
     *   payment_pending— any receipt pending/overdue
     *   penalty_issued — at least one receipt or asset exists
     *   warning_issued — at least one warning issued, no receipts
     *   open           — nothing yet
     */
    protected function recomputeStatus(?ViolationRecord $record): void
    {
        if (! $record) {
            return;
        }

        $receipts = $record->penaltyReceipts()->get(['payment_status']);
        $warnings = $record->warningLetters()->get(['complied']);
        $hasAsset = $record->confiscatedAssets()->exists();

        $byStatus = fn (string $s) => $receipts->contains(fn ($r) => $r->payment_status === $s);

        $newStatus = match (true) {
            $receipts->isNotEmpty()
                && $receipts->every(fn ($r) => in_array($r->payment_status, ['paid', 'court_paid'], true))
                && ($warnings->isEmpty() || $warnings->every(fn ($w) => (bool) $w->complied))
                    => 'closed',

            $byStatus('court_filed')
                    => 'court_filed',

            $byStatus('overdue') || $byStatus('pending')
                    => 'payment_pending',

            $byStatus('paid') || $byStatus('court_paid')
                    => 'paid',

            $receipts->isNotEmpty() || $hasAsset
                    => 'penalty_issued',

            $warnings->isNotEmpty()
                    => 'warning_issued',

            default
                    => 'open',
        };

        if ($record->status !== $newStatus) {
            $record->update(['status' => $newStatus]);
        }
    }

    protected function notifier(): ?ViolatorNotifier
    {
        try {
            return App::make(ViolatorNotifier::class);
        } catch (\Throwable $e) {
            Log::error('[SMS] ViolatorNotifier could not be resolved', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
