<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('shift_assignments_t')) {
            return;
        }

        Schema::table('shift_assignments_t', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('shift_assignments_t')) {
            return;
        }

        Schema::table('shift_assignments_t', function (Blueprint $table) {
            //
        });
    }
};
