<?php

namespace App\Console\Commands;

use App\Models\ConfiscatedAsset;
use App\Models\PenaltyReceipt;
use App\Models\WarningLetter;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EscalatePenalties extends Command
{
    protected $signature = 'penalties:escalate';

    protected $description = 'Auto-escalate overdue penalties, expired warnings, and overdue asset transfers';

    public function handle(): int
    {
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();

        // Spec ¶113: pay within 3 days OR court case with doubled fine.
        // Only escalate receipts already marked 'overdue' by penalty:check-overdue (runs at 07:00).
        // This gives violators a 1-day "overdue" SMS warning before court action.
        $courtEligible = PenaltyReceipt::where('payment_status', 'overdue')
            ->where('is_court_case', false)
            ->where('payment_deadline', '<', $today)
            ->get();

        foreach ($courtEligible as $receipt) {
            $doubled = $receipt->fine_amount * 2;

            $receipt->update([
                'payment_status'     => 'court_filed',
                'is_court_case'      => true,
                'court_filed_date'   => $now,
                'court_fine_amount'  => $doubled,
            ]);

            if ($record = $receipt->violationRecord) {
                $record->update([
                    'action_taken' => ($record->action_taken ?? '') . "\nበራስ-ሰር ወደ ፍ/ቤት ተላልፏል። ቅጣት እጥፍ ሆኗል ({$doubled} ብር)።",
                ]);
            }
        }

        $this->info("Court escalations: {$courtEligible->count()}");

        $expiredWarnings = WarningLetter::where('complied', false)
            ->where(function ($q) {
                $q->whereNull('escalated_to_task_force')
                  ->orWhere('escalated_to_task_force', false);
            })
            ->where('deadline', '<', $now)
            ->get();

        $escalatedCount = 0;
        foreach ($expiredWarnings as $warning) {
            $warning->update([
                'escalated_to_task_force' => true,
                'escalation_date'         => $now,
            ]);

            if ($record = $warning->violationRecord) {
                $record->update([
                    'action_taken' => ($record->action_taken ?? '') . "\nየማስጠንቀቂያ ገደብ አልፏል። ወደ ግብረ ኃይል ተላልፏል።",
                ]);
            }

            $escalatedCount++;
        }

        $this->info("Warning escalations to task force: {$escalatedCount}");

        $overdueTransfers = ConfiscatedAsset::where('status', 'handed_over')
            ->where('is_perishable', false)
            ->whereNotNull('handover_date')
            ->where('handover_date', '<', $now->copy()->subDays(3))
            ->get();

        foreach ($overdueTransfers as $asset) {
            if (! str_contains($asset->notes ?? '', 'የማስተላለፊያ ገደብ አልፏል')) {
                $asset->update([
                    'notes' => ($asset->notes ?? '') . "\n⚠ የ3 ቀን የማስተላለፊያ ገደብ አልፏል! ወደ ክ/ከተማ ግምጃ ቤት ማስተላለፍ ያስፈልጋል።",
                ]);
            }
        }

        $this->info("Overdue asset transfers flagged: {$overdueTransfers->count()}");
        $this->info('Escalation complete.');

        return self::SUCCESS;
    }
}
