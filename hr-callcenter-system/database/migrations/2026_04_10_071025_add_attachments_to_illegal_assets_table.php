<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->json('attachments')->nullable();
        });

        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->json('attachments')->nullable();
        });

        Schema::table('asset_sales', function (Blueprint $table) {
            $table->json('attachments')->nullable();
        });

        Schema::table('asset_disposals', function (Blueprint $table) {
            $table->json('attachments')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
        Schema::table('asset_transfers', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
        Schema::table('asset_sales', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
        Schema::table('asset_disposals', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
