<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'attendance_date')) {
                $table->date('attendance_date')->nullable()->after('shift_assignment_id');
            }
        });

        // Backfill existing rows.
        DB::statement("
            UPDATE attendances
            SET attendance_date = DATE(COALESCE(check_in, created_at))
            WHERE attendance_date IS NULL
        ");

        DB::statement("
            UPDATE attendances a
            JOIN shift_assignments sa ON sa.id = a.shift_assignment_id
            SET a.attendance_date = sa.assigned_date
            WHERE a.attendance_date IS NULL
        ");

        // Make attendance_date required after backfill.
        DB::statement('ALTER TABLE attendances MODIFY attendance_date DATE NOT NULL');

        // Ensure FK-required index exists before dropping old unique index.
        try {
            DB::statement('CREATE INDEX attendances_shift_assignment_id_idx ON attendances (shift_assignment_id)');
        } catch (\Throwable $e) {
            // already exists
        }

        // Replace unique key from per-assignment to per-assignment-per-day.
        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_employee_shift_unique');
        } catch (\Throwable $e) {
            // already removed / missing
        }

        try {
            DB::statement('ALTER TABLE attendances ADD UNIQUE attendances_assignment_date_unique (shift_assignment_id, attendance_date)');
        } catch (\Throwable $e) {
            // already exists
        }

        try {
            DB::statement('CREATE INDEX attendances_employee_date_index ON attendances (employee_id, attendance_date)');
        } catch (\Throwable $e) {
            // already exists
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_assignment_date_unique');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_employee_date_index');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('ALTER TABLE attendances ADD UNIQUE attendances_employee_shift_unique (employee_id, shift_assignment_id)');
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'attendance_date')) {
                $table->dropColumn('attendance_date');
            }
        });
    }
};

