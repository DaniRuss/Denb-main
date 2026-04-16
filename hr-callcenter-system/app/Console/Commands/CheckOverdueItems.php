<?php

namespace App\Console\Commands;

use App\Models\ConfiscatedAsset;
use App\Models\PenaltyReceipt;
use App\Models\User;
use App\Models\WarningLetter;
use App\Notifications\PaymentOverdueNotification;
use App\Notifications\TransferOverdueNotification;
use App\Notifications\WarningDeadlineNotification;
use Illuminate\Console\Command;

class CheckOverdueItems extends Command
{
    protected $signature = 'penalty:check-overdue';

    protected $description = 'Check for overdue payments, expired warnings, and overdue asset transfers, then notify supervisors.';

    public function handle(): int
    {
        $supervisors = User::role(['admin', 'supervisor'])->get();

        if ($supervisors->isEmpty()) {
            $this->warn('No admin/supervisor users found to notify.');
            return self::SUCCESS;
        }

        $count = 0;

        $overdueReceipts = PenaltyReceipt::where('payment_status', 'pending')
            ->where('payment_deadline', '<', now()->toDateString())
            ->with('violationRecord')
            ->get();

        foreach ($overdueReceipts as $receipt) {
            $alreadyNotified = $receipt->violationRecord?->reportedByUser
                ? $receipt->violationRecord->reportedByUser
                    ->notifications()
                    ->where('type', PaymentOverdueNotification::class)
                    ->where('data->receipt_id', $receipt->id)
                    ->exists()
                : false;

            if (! $alreadyNotified) {
                $reporter = $receipt->violationRecord?->reportedByUser;
                $reporter?->notify(new PaymentOverdueNotification($receipt));

                foreach ($supervisors as $supervisor) {
                    if ($supervisor->id !== $reporter?->id) {
                        $supervisor->notify(new PaymentOverdueNotification($receipt));
                    }
                }
                $count++;
            }
        }

        $expiredWarnings = WarningLetter::where('complied', false)
            ->where('deadline', '<', now())
            ->with('violationRecord')
            ->get();

        foreach ($expiredWarnings as $letter) {
            $reporter = $letter->violationRecord?->reportedByUser;
            $alreadyNotified = $reporter
                ? $reporter->notifications()
                    ->where('type', WarningDeadlineNotification::class)
                    ->where('data->warning_letter_id', $letter->id)
                    ->exists()
                : false;

            if (! $alreadyNotified) {
                $reporter?->notify(new WarningDeadlineNotification($letter));

                foreach ($supervisors as $supervisor) {
                    if ($supervisor->id !== $reporter?->id) {
                        $supervisor->notify(new WarningDeadlineNotification($letter));
                    }
                }
                $count++;
            }
        }

        $overdueAssets = ConfiscatedAsset::whereNotNull('transfer_deadline')
            ->whereNull('transferred_date')
            ->where('transfer_deadline', '<', now()->toDateString())
            ->with('violationRecord')
            ->get();

        foreach ($overdueAssets as $asset) {
            $seizer = $asset->seizedByUser;
            $alreadyNotified = $seizer
                ? $seizer->notifications()
                    ->where('type', TransferOverdueNotification::class)
                    ->where('data->confiscated_asset_id', $asset->id)
                    ->exists()
                : false;

            if (! $alreadyNotified) {
                $seizer?->notify(new TransferOverdueNotification($asset));

                foreach ($supervisors as $supervisor) {
                    if ($supervisor->id !== $seizer?->id) {
                        $supervisor->notify(new TransferOverdueNotification($asset));
                    }
                }
                $count++;
            }
        }

        $this->info("Sent notifications for {$count} overdue items.");

        return self::SUCCESS;
    }
}
