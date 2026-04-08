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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('walkie_talkie_serial')->nullable()->after('t_shirt_size');
            $table->boolean('stick_issued')->default(false)->after('walkie_talkie_serial');
            $table->text('other_equipment')->nullable()->after('stick_issued');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['walkie_talkie_serial', 'stick_issued', 'other_equipment']);
        });
    }
};
