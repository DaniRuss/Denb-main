<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confiscated_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('violation_record_id')->constrained('violation_records')->restrictOnDelete();

            // Asset details
            $table->string('description');                      // what was seized
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit')->nullable();                 // pcs, kg, etc.
            $table->boolean('is_perishable')->default(false);

            // Seizure
            $table->date('seized_date');
            $table->string('seizure_receipt_number', 50)->nullable();
            $table->foreignId('seized_by')->constrained('users')->restrictOnDelete();

            // Handover to woreda asset office
            $table->date('handover_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();

            // Estimation
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->date('estimation_date')->nullable();

            // Transfer to sub-city (within 3 days for non-perishable)
            $table->date('transfer_deadline')->nullable();
            $table->date('transferred_date')->nullable();
            $table->foreignId('transferred_to_sub_city_id')->nullable()->constrained('sub_cities')->nullOnDelete();

            // Auction / Sale
            $table->decimal('sold_amount', 12, 2)->nullable();
            $table->date('sold_date')->nullable();

            // Disposal (for unsellable/damaged items)
            $table->date('disposed_date')->nullable();
            $table->string('disposal_reason')->nullable();

            // Revenue split (60/40)
            $table->decimal('authority_share', 12, 2)->nullable();  // 60%
            $table->decimal('city_finance_share', 12, 2)->nullable(); // 40%

            // Status lifecycle
            $table->string('status', 30)->default('seized');
            // seized → handed_over → estimated → transferred → sold | disposed

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('violation_record_id');
            $table->index('status');
            $table->index('seized_date');
            $table->index('transfer_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confiscated_assets');
    }
};
