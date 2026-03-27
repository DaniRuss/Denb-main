<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add offline-sync columns to awareness_engagements.
     *
     * created_at_mobile — when the officer ACTUALLY filled it in the field (device clock)
     * synced_at         — when the record hit the server
     * local_uuid        — UUID generated on the device (prevents duplicate conflicts)
     * is_offline_draft  — true if the record was saved to IndexedDB before syncing
     */
    public function up(): void
    {
        Schema::table('awareness_engagements', function (Blueprint $table) {
            // UUID generated on device — used as collision-safe idempotency key
            $table->uuid('local_uuid')->nullable()->unique()->after('engagement_code');

            // "Real" field time vs server receipt time
            $table->timestamp('created_at_mobile')->nullable()->after('session_datetime');
            $table->timestamp('synced_at')->nullable()->after('created_at_mobile');

            // Was this record initially saved offline?
            $table->boolean('is_offline_draft')->default(false)->after('synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('awareness_engagements', function (Blueprint $table) {
            $table->dropColumn(['local_uuid', 'created_at_mobile', 'synced_at', 'is_offline_draft']);
        });
    }
};
