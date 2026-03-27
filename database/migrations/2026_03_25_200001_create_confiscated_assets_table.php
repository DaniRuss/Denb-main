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
        Schema::create('confiscated_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_tip_id')->constrained()->cascadeOnDelete();
            $table->string('item_description');
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('seizure_location');
            $table->foreignId('seized_by')->constrained('users');
            $table->date('seizure_date');
            $table->enum('handover_status', ['impounded', 'auctioned', 'destroyed', 'returned'])->default('impounded');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confiscated_assets');
    }
};
