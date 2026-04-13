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

        // 1. Penalty Receipts: pending → overdue (payment deadline passed)
        $overdueReceipts = PenaltyReceipt::where('payment_status', 'pending')
            ->where('payment_deadline', '<', $now)
            ->get();

        foreach ($overdueReceipts as $receipt) {
            $receipt->update(['payment_status' => 'overdue']);

            if ($record = $receipt->violationRecord) {
                $record->update([
                    'status' => 'payment_pending',
                    'action_taken' => ($record->action_taken ?? '') . "\nየ3 ቀን የክፍያ ገደብ አልፏል። ሁኔታ: ያልተከፈለ።",
                ]);
            }
        }

        $this->info("Overdue receipts: {$overdueReceipts->count()}");

        // 2. Penalty Receipts: overdue → court_filed, fine doubled
        // Doc: "በ3ቀን ጊዜ ውስጥ ገቢ ያላደረገ" → court immediately after 3-day deadline expires
        $courtEligible = PenaltyReceipt::where('payment_status', 'overdue')
            ->where('is_court_case', false)
            ->where('payment_deadline', '<', $now)
            ->get();

        foreach ($courtEligible as $receipt) {
            $doubled = $receipt->fine_amount * 2;

            $receipt->update([
                'payment_status' => 'court_filed',
                'is_court_case' => true,
                'court_filed_date' => $now,
                'court_fine_amount' => $doubled,
            ]);

            if ($record = $receipt->violationRecord) {
                $record->update([
                    'status' => 'court_filed',
                    'action_taken' => ($record->action_taken ?? '') . "\nበራስ-ሰር ወደ ፍ/ቤት ተላልፏል። ቅጣት እጥፍ ሆኗል ({$doubled} ብር)።",
                ]);
            }
        }

        $this->info("Court escalations: {$courtEligible->count()}");

        // 3. Warning Letters: expired + not complied → escalate to task force
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
                'escalation_date' => $now,
            ]);

            if ($record = $warning->violationRecord) {
                $record->update([
                    'action_taken' => ($record->action_taken ?? '') . "\nየማስጠንቀቂያ ገደብ አልፏል። ወደ ግብረ ኃይል ተላልፏል።",
                ]);
            }

            $escalatedCount++;
        }

        $this->info("Warning escalations to task force: {$escalatedCount}");

        // 4. Confiscated Assets: flag overdue transfers (3+ days since handover)
        $overdueTransfers = ConfiscatedAsset::where('status', 'handed_over')
            ->where('is_perishable', false)
            ->whereNotNull('handover_date')
            ->where('handover_date', '<', $now->copy()->subDays(3))
            ->get();

        foreach ($overdueTransfers as $asset) {
            if (! str_contains($asset->notes ?? '', 'የማስተላለፊያ ገደብ አልፏል')) {
                $asset->update([
                    'notes' => ($asset->notes ?? '') . "\n⚠ የ3 ቀን የማስተላለፊያ ገደብ አልፏል! ወደ ክ/ከተማ ���ምጃ ቤት ማስተላለፍ ያስፈልጋል።",
                ]);
            }
        }

        $this->info("Overdue asset transfers flagged: {$overdueTransfers->count()}");

        $this->info('Escalation complete.');

        return self::SUCCESS;
    }
}
