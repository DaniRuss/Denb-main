<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'location_type')) {
                $table->string('location_type', 30)->default('sub_city')->after('emergency_contact');
            }
        });

        if (Schema::hasColumn('employees', 'sub_city_id')) {
            DB::statement('ALTER TABLE employees MODIFY sub_city_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('employees', 'woreda_id')) {
            DB::statement('ALTER TABLE employees MODIFY woreda_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'location_type')) {
                $table->dropColumn('location_type');
            }
        });
    }
};
