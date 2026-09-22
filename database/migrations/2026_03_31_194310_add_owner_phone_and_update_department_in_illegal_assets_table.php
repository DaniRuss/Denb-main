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
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->string('owner_phone')->nullable()->after('owner_name');
            $table->foreignId('department_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('illegal_assets', function (Blueprint $table) {
            $table->dropColumn('owner_phone');
            $table->foreignId('department_id')->nullable(false)->change();
        });
    }
};
