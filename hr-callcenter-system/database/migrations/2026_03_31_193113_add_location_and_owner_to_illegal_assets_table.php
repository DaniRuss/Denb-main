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
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->string('owner_name')->nullable()->after('description');
            $table->foreignId('sub_city_id')->nullable()->constrained('sub_cities')->nullOnDelete();
            $table->foreignId('woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
            $table->string('kebele')->nullable();
            $table->string('house_number')->nullable();
            
            // Make location_found nullable if it isn't already
            $table->string('location_found')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->dropForeign(['sub_city_id']);
            $table->dropForeign(['woreda_id']);
            $table->dropColumn(['owner_name', 'sub_city_id', 'woreda_id', 'kebele', 'house_number']);
            $table->string('location_found')->nullable(false)->change();
        });
    }
};
