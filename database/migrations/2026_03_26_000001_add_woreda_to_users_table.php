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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('woreda_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('sub_city_id')->nullable()->after('woreda_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reversing the foreign keys
            $table->dropForeign(['sub_city_id']);
            $table->dropForeign(['woreda_id']);
            
            // Dropping columns
            $table->dropColumn(['sub_city_id', 'woreda_id']);
        });
    }
};
