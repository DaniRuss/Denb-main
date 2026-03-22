<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Officer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DataSyncSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure at least one department exists
        $department = Department::firstOrCreate(
            ['code' => 'GEN-01'],
            [
                'name_en' => 'General Department',
                'name_am' => 'ጠቅላላ መምሪያ',
                'description' => 'Default department for synced officers',
            ]
        );

        echo "Department created/verified: " . $department->name_en . "\n";

        // 2. Sync Employees to Officers
        // We only sync employees who have a valid user_id and don't already have an officer record
        $employees = Employee::whereNotNull('user_id')->get();
        
        foreach ($employees as $employee) {
            // Check if user exists
            $user = User::find($employee->user_id);
            if (!$user) {
                echo "Skipping employee {$employee->first_name_en} - User ID {$employee->user_id} not found.\n";
                continue;
            }

            $officer = Officer::firstOrCreate(
                ['user_id' => $employee->user_id],
                [
                    'badge_number' => $employee->employee_id ?? 'B-' . str_pad($employee->id, 5, '0', STR_PAD_LEFT),
                    'department_id' => $department->id,
                    'rank' => $employee->rank ?? 'Officer',
                    'rank_am' => $employee->rank ?? 'ኦፊሰር',
                    'specialization' => $employee->specialization ?? 'General',
                    'phone' => $employee->phone,
                    'status' => 'active',
                    'date_joined' => $employee->hire_date ?? now(),
                ]
            );

            if ($officer->wasRecentlyCreated) {
                echo "Officer created for: " . $user->name . " (Badge: " . $officer->badge_number . ")\n";
            } else {
                echo "Officer already exists for: " . $user->name . "\n";
            }
        }
    }
}
