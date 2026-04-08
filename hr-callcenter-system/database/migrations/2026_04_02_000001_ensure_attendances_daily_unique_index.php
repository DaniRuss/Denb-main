<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One attendance row per shift assignment per calendar day (daily history until end_date).
 * Drops legacy unique (employee_id, shift_assignment_id) if still present.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_employee_shift_unique');
        } catch (\Throwable) {
            // already removed
        }

        try {
            DB::statement('ALTER TABLE attendances ADD UNIQUE attendances_assignment_date_unique (shift_assignment_id, attendance_date)');
        } catch (\Throwable) {
            // already exists
        }

        try {
            DB::statement('CREATE INDEX attendances_employee_date_index ON attendances (employee_id, attendance_date)');
        } catch (\Throwable) {
            // already exists
        }

        try {
            DB::statement('CREATE INDEX attendances_shift_assignment_id_idx ON attendances (shift_assignment_id)');
        } catch (\Throwable) {
            // already exists
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_assignment_date_unique');
        } catch (\Throwable) {
        }

        try {
            DB::statement('ALTER TABLE attendances ADD UNIQUE attendances_employee_shift_unique (employee_id, shift_assignment_id)');
        } catch (\Throwable) {
        }
    }
};
