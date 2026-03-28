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
        // Fix awareness_engagements table
        Schema::table('awareness_engagements', function (Blueprint $table) {
            $table->longText('violation_photo_path')->nullable()->change();
        });

        // Fix volunteer_tips table
        Schema::table('volunteer_tips', function (Blueprint $table) {
            $table->longText('volunteer_signature_path')->nullable()->change();
            if (!Schema::hasColumn('volunteer_tips', 'evidence_photo')) {
                $table->longText('evidence_photo')->nullable()->after('volunteer_signature_path');
            } else {
                $table->longText('evidence_photo')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awareness_engagements', function (Blueprint $table) {
            $table->string('violation_photo_path')->nullable()->change();
        });

        Schema::table('volunteer_tips', function (Blueprint $table) {
            $table->string('volunteer_signature_path')->nullable()->change();
            // We keep evidence_photo as longText if we want to revert, 
            // but we can't easily change it back to non-existent without dropping.
        });
    }
};
