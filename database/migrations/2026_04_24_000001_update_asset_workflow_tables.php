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
            if (!Schema::hasColumn('asset_handovers', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('asset_handovers', 'attachments')) {
                $table->json('attachments')->nullable();
            }
        });

        Schema::table('asset_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_transfers', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('asset_transfers', 'attachments')) {
                $table->json('attachments')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_handovers', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'attachments']);
        });

        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'attachments']);
        });
    }
};
