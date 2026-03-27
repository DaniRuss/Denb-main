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
        Schema::table('volunteer_tips', function (Blueprint $table) {
            // we change the enum to a string to flexibly support:
            // draft, pending_verification, verified, resolved, dismissed
            $table->string('status')->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_tips', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
