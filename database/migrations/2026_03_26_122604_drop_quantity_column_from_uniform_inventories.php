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
        Schema::table('uniform_inventories', function (Blueprint $table) {
            if (Schema::hasColumn('uniform_inventories', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uniform_inventories', function (Blueprint $table) {
            $table->integer('quantity')->nullable();
        });
    }
};
