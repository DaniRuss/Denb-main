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
            ->whereDate('assigned_date', $date->toDateString())
            ->get();

        $created = 0;

        foreach ($assignments as $assignment) {
            $exists = Attendance::query()
                ->where('employee_id', $assignment->employee_id)
                ->where('shift_assignment_id', $assignment->id)
                ->exists();

            if ($exists) {
                continue;
            }

            Attendance::create([
                'employee_id' => $assignment->employee_id,
                'shift_assignment_id' => $assignment->id,
                'check_in' => null,
                'check_out' => null,
                'attendance_status' => 'absent',
                'auto_generated' => true,
                'remarks' => 'Auto-generated absence',
            ]);

            $created++;
        }

        $this->info("Created {$created} absence records.");

        return self::SUCCESS;
    }
}

