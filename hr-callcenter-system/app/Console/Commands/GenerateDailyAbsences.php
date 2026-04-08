<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ShiftAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDailyAbsences extends Command
{
    protected $signature = 'attendance:generate-absences {date?}';

    protected $description = 'Generate absent attendance records for shifts without attendance.';

    public function handle(): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))->startOfDay()
            : now()->subDay()->startOfDay();

        $this->info('Generating absences for date: ' . $date->toDateString());

        $assignments = ShiftAssignment::query()
            ->where('status', 'scheduled')
            ->whereDate('assigned_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->get();

        $created = 0;

        foreach ($assignments as $assignment) {
            $attendance = Attendance::query()->firstOrCreate(
                [
                    'employee_id' => $assignment->employee_id,
                    'shift_assignment_id' => $assignment->id,
                    'attendance_date' => $date->toDateString(),
                ],
                [
                    'check_in' => null,
                    'check_out' => null,
                    'attendance_status' => Attendance::STATUS_PENDING,
                    'auto_generated' => false,
                ]
            );

            if (! $attendance->check_in) {
                $attendance->attendance_status = Attendance::STATUS_ABSENT;
                $attendance->auto_generated = true;
                $attendance->remarks = $attendance->remarks ?: 'Auto-generated absence';
                $attendance->save();
            }

            $created++;
        }

        $this->info("Created {$created} absence records.");

        return self::SUCCESS;
    }
}

