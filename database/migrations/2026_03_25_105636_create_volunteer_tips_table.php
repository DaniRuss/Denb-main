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
        Schema::create('volunteer_tips', function (Blueprint $table) {
            $table->id();
            $table->string('tip_code')->unique(); // TIP-20260325-001

            // Link to the engagement that generated this tip (optional — tip can stand alone)
            $table->foreignId('engagement_id')->nullable()->constrained('awareness_engagements')->nullOnDelete();

            // Suspect Information
            $table->string('suspect_name')->nullable();
            $table->enum('violation_type', [
                'illegal_land_invasion', 'illegal_construction', 'illegal_expansion',
                'illegal_waste_disposal', 'road_safety', 'illegal_trade',
                'illegal_animal_trade', 'disturbing_acts', 'illegal_advertisement',
            ]);
            $table->string('violation_location');     // ቀጣና / Block / precise address
            $table->foreignId('sub_city_id')->constrained();
            $table->foreignId('woreda_id')->constrained();
            $table->string('block_number')->nullable();
            $table->date('violation_date');
            $table->date('reported_date');

            // Volunteer Reporter — ጥቆማ ያቀረበ
            $table->string('volunteer_name')->nullable();      // may be anonymous
            $table->boolean('is_anonymous')->default(false);
            $table->string('volunteer_signature_path')->nullable(); // scanned/photo path

            // Intake
            $table->foreignId('received_by')->constrained('users'); // Paramilitary who logged it

            // Approval chain (mirrors engagement workflow)
            $table->enum('status', [
                'pending',        // logged, awaiting Woreda Coordinator review
                'verified',       // Coordinator approved — visible to Officer
                'investigating',  // Officer has picked up
                'action_taken',   // Officer closed with an action
                'false_report',   // Coordinator or Officer dismissed
            ])->default('pending');

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Enforcement (Officer layer)
            $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action_taken', [
                'formal_warning',
                'financial_penalty',
                'asset_confiscation',
                'legal_referral',
                'no_action',
            ])->nullable();
            $table->text('action_notes')->nullable();           // የተወሰደ እርምጃ - full notes
            $table->date('action_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_tips');
    }
};
