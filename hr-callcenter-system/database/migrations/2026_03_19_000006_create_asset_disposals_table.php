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
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->enum('disposal_method', ['Destruction', 'Recycling', 'Government storage', 'Other']);
            $table->date('disposal_date');
            $table->foreignId('disposed_by_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
