<?php

namespace App\Console\Commands;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RotateOfficerShifts extends Command
{
    protected $signature = 'shifts:rotate-officers';

    protected $description = 'Automatically rotate officer shifts when 30-day periods end';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        ShiftAssignment::query()
            ->whereDate('end_date', '<', $today)
            ->where('status', 'scheduled')
            ->chunkById(100, function ($assignments) {
                foreach ($assignments as $assignment) {
                    $nextShift = $this->determineNextShift($assignment->shift);

                    if (! $nextShift) {
                        continue;
                    }

                    $start = Carbon::today();
                    $end   = $start->copy()->addDays(29);

                    ShiftAssignment::create([
                        'employee_id'   => $assignment->employee_id,
                        'shift_id'      => $nextShift->id,
                        'block'         => $assignment->block,
                        'assigned_date' => $start,
                        'end_date'      => $end,
                        'assigned_by'   => $assignment->assigned_by,
                        'status'        => 'scheduled',
                    ]);

                    $assignment->update(['status' => 'completed']);
                }
            });

        $this->info('Officer shifts rotated where periods ended.');

        return Command::SUCCESS;
    }

    protected function determineNextShift(?Shift $current): ?Shift
    {
        if (! $current) {
            return null;
        }

        $all = Shift::query()
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get()
            ->values();

        if ($all->isEmpty()) {
            return null;
        }

        $index = $all->search(fn ($s) => $s->id === $current->id);

        if ($index === false) {
            return $all->first();
        }

	$nextIndex = ($index + 1) % $all->count();

        return $all[$nextIndex];
    }
}

