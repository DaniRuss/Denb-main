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
            $table->string('shoe_size_casual')->nullable()->change();
            $table->string('shoe_size_leather')->nullable()->change();
            $table->string('hat_size')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('shoe_size_casual')->nullable()->change();
            $table->integer('shoe_size_leather')->nullable()->change();
            $table->integer('hat_size')->nullable()->change();
        });
    }
};
