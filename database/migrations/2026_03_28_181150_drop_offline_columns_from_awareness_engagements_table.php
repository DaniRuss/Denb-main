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
        Schema::table('awareness_engagements', function (Blueprint $table) {
            $table->dropColumn(['local_uuid', 'created_at_mobile', 'synced_at', 'is_offline_draft']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awareness_engagements', function (Blueprint $table) {
            $table->uuid('local_uuid')->nullable();
            $table->timestamp('created_at_mobile')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->boolean('is_offline_draft')->default(false);
        });
    }
};
