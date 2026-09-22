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
        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->foreignId('to_woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
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
        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->dropForeign(['to_woreda_id']);
            $table->dropForeign(['confirmed_by_user_id']);
            $table->dropColumn([
                'to_woreda_id',
                'confirmation_status',
                'confirmed_by_user_id',
                'confirmed_at',
            ]);
        });
    }
};
