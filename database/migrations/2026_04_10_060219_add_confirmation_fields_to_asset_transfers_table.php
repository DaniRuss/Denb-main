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
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->foreignId('from_woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
            $table->foreignId('to_sub_city_id')->nullable()->constrained('sub_cities')->nullOnDelete();
            $table->string('confirmation_status')->default('Confirmed'); // Defaults to Confirmed unless explicitly pending
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropForeign(['from_woreda_id']);
            $table->dropForeign(['to_sub_city_id']);
            $table->dropForeign(['confirmed_by_user_id']);
            $table->dropColumn([
                'from_woreda_id',
                'to_sub_city_id',
                'confirmation_status',
                'confirmed_by_user_id',
                'confirmed_at',
            ]);
        });
    }
};
