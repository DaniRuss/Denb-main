<?php

namespace App\Observers;

use App\Models\ConfiscatedAsset;
use App\Models\PenaltyReceipt;
use App\Models\ViolationRecord;
use App\Models\WarningLetter;

class ViolationStatusObserver
{
    public function createdReceipt(PenaltyReceipt $receipt): void
    {
        $record = $receipt->violationRecord;

        if (! $record || in_array($record->status, ['paid', 'court_filed', 'closed'])) {
            return;
        }

        if ($record->status === 'open' || $record->status === 'warning_issued') {
            $record->update(['status' => 'penalty_issued']);
        }
    }

    public function updatedReceipt(PenaltyReceipt $receipt): void
    {
        if (! $receipt->wasChanged('payment_status')) {
            return;
        }

        $record = $receipt->violationRecord;

        if (! $record) {
            return;
        }

        match ($receipt->payment_status) {
            'paid', 'court_paid' => $record->update(['status' => 'paid']),
            'court_filed' => $record->update(['status' => 'court_filed']),
            'pending', 'overdue' => $record->update(['status' => 'payment_pending']),
            default => null,
        };
    }

    public function createdWarning(WarningLetter $letter): void
    {
        $record = $letter->violationRecord;

        if (! $record || $record->status !== 'open') {
            return;
        }

        $record->update(['status' => 'warning_issued']);
    }

    public function createdAsset(ConfiscatedAsset $asset): void
    {
        $record = $asset->violationRecord;

        if (! $record || in_array($record->status, ['paid', 'court_filed', 'closed'])) {
            return;
        }

        if ($record->status === 'open') {
            $record->update(['status' => 'penalty_issued']);
        }
    }
}
