<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shift_assignments')) {
            return;
        }

        if (Schema::hasColumn('shift_assignments', 'block')) {
            return;
        }

        if (! Schema::hasColumn('shift_assignments', 'zone')) {
            return;
        }

        // Prefer native rename when available (requires doctrine/dbal for some drivers).
        $renameException = null;
        try {
            Schema::table('shift_assignments', function (Blueprint $table) {
                $table->renameColumn('zone', 'block');
            });

            return;
        } catch (\Throwable $e) {
            // Fall back to driver-specific SQL below.
            $renameException = $e;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // `zone` was created as string, default length 255.
            DB::statement('ALTER TABLE `shift_assignments` CHANGE `zone` `block` VARCHAR(255) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE shift_assignments RENAME COLUMN zone TO block');
            return;
        }

        if ($driver === 'sqlite') {
            // SQLite column rename supported on modern versions.
            DB::statement('ALTER TABLE shift_assignments RENAME COLUMN zone TO block');
            return;
        }

        throw $renameException ?? new RuntimeException('Unsupported database driver for renaming shift_assignments.zone to block.');
    }

    public function down(): void
    {
        if (! Schema::hasTable('shift_assignments')) {
            return;
        }

        if (Schema::hasColumn('shift_assignments', 'zone')) {
            return;
        }

        if (! Schema::hasColumn('shift_assignments', 'block')) {
            return;
        }

        $renameException = null;
        try {
            Schema::table('shift_assignments', function (Blueprint $table) {
                $table->renameColumn('block', 'zone');
            });

            return;
        } catch (\Throwable $e) {
            // Fall back to driver-specific SQL below.
            $renameException = $e;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `shift_assignments` CHANGE `block` `zone` VARCHAR(255) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE shift_assignments RENAME COLUMN block TO zone');
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE shift_assignments RENAME COLUMN block TO zone');
            return;
        }

        throw $renameException ?? new RuntimeException('Unsupported database driver for renaming shift_assignments.block to zone.');
    }
};

