<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // Block number / identifier (for House-to-House and Coffee Ceremony)
            $table->string('block')->nullable()->after('woreda_id');
            // Rename target_location → specific_place for clarity
            $table->renameColumn('target_location', 'specific_place');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('block');
            $table->renameColumn('specific_place', 'target_location');
        });
    }
};
