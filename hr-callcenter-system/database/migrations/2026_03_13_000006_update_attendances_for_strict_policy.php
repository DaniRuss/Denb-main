<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'status_locked')) {
                $table->boolean('status_locked')->default(false)->after('attendance_status');
            }

            if (! Schema::hasColumn('attendances', 'verified_by')) {
                $table->foreignId('verified_by')
                    ->nullable()
                    ->after('status_locked')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('attendances', 'verified_at')) {
                $table->dateTime('verified_at')->nullable()->after('verified_by');
            }

            if (! Schema::hasColumn('attendances', 'auto_generated')) {
                $table->boolean('auto_generated')->default(false)->after('verified_at');
            }

            if (! Schema::hasColumn('attendances', 'check_in_location')) {
                $table->string('check_in_location')->nullable()->after('check_out');
            }

            // One attendance per employee & shift assignment.
            $table->unique(['employee_id', 'shift_assignment_id'], 'attendances_employee_shift_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'check_in_location')) {
                $table->dropColumn('check_in_location');
            }

            if (Schema::hasColumn('attendances', 'auto_generated')) {
                $table->dropColumn('auto_generated');
            }

            if (Schema::hasColumn('attendances', 'verified_at')) {
                $table->dropColumn('verified_at');
            }

            if (Schema::hasColumn('attendances', 'verified_by')) {
                $table->dropConstrainedForeignId('verified_by');
            }

            if (Schema::hasColumn('attendances', 'status_locked')) {
                $table->dropColumn('status_locked');
            }

            $table->dropUnique('attendances_employee_shift_unique');
        });
    }
};

