<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM column to VARCHAR to accommodate evolving workflow states smoothly.
        DB::statement("ALTER TABLE illegal_assets MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Registered'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Fallback or leave as VARCHAR
    }
};
