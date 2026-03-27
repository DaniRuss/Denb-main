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
            $table->string('violation_photo_path')->nullable()->after('rejection_note');
            $table->longText('officer_signature')->nullable()->after('violation_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awareness_engagements', function (Blueprint $table) {
            $table->dropColumn(['violation_photo_path', 'officer_signature']);
        });
    }


};
