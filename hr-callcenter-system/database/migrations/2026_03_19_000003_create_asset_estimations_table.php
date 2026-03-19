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
        Schema::create('asset_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illegal_asset_id')->constrained('illegal_assets')->cascadeOnDelete();
            $table->decimal('estimated_value', 15, 2);
            $table->string('evaluator_name');
            $table->date('evaluation_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_estimations');
    }
};
