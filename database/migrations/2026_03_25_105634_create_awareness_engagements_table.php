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
        Schema::create('awareness_engagements', function (Blueprint $table) {
            $table->id();
            $table->string('engagement_code')->unique(); // ENG-20260325-001

            // Link to campaign
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();

            // Type determines which sub-fields are required (enforced in Filament form)
            $table->enum('engagement_type', [
                'house_to_house',   // ቤት ለቤት
                'coffee_ceremony',  // ቡና ጠጡ
                'organization',     // በአደረጃጀት
            ]);

            // Geography - reuse existing FK pattern from employees table
            $table->foreignId('sub_city_id')->constrained();
            $table->foreignId('woreda_id')->constrained();
            $table->string('block_number')->nullable();         // ብሎክ ቁጥር

            // Violation focus — The 9 code violation types (taxonomy)
            $table->enum('violation_type', [
                'illegal_land_invasion',       // በህገ-ወጥ መሬት ወረራ
                'illegal_construction',        // በህገ-ወጥ ግንባታ
                'illegal_expansion',           // በህገ-ወጥ ማስፋፋት
                'illegal_waste_disposal',      // በህገ-ወጥ ደረቅ እና ፍሳሽ ማስወገድ
                'road_safety',                 // መንገድ ደህንነት
                'illegal_trade',               // በህገ-ወጥ ንግድ
                'illegal_animal_trade',        // በህገ-ወጥ የእንስሳት ዝውውር/ዕርድ
                'disturbing_acts',             // በአዋኪ ድርጊት
                'illegal_advertisement',       // በህገ-ወጥ ማስታወቂያ
            ]);

            // Recurrence tracking — ለስንተኛ ግዜ
            $table->unsignedTinyInteger('round_number')->default(1);

            // ── House-to-House specific (nullable; populated when type = house_to_house)
            $table->string('citizen_name')->nullable();
            $table->enum('citizen_gender', ['male', 'female'])->nullable();
            $table->unsignedTinyInteger('citizen_age')->nullable();

            // ── Coffee Ceremony specific (nullable; populated when type = coffee_ceremony)
            $table->unsignedSmallInteger('headcount')->nullable();
            $table->string('stakeholder_partner')->nullable(); // ባለድርሻ አካል

            // ── Organization specific (nullable; populated when type = organization)
            $table->enum('organization_type', [
                'womens_association',    // ሴት ማህበር
                'youth_association',     // ወጣት ማህበር
                'edir',                  // እድር
                'religious_institution', // የሀይማኖት ተቋማት
                'block_leaders',         // ብሎክ አመራሮች
                'peace_army',            // የሰላም ሰራዊት
                'equb',                  // እቁብ
            ])->nullable();
            $table->unsignedSmallInteger('org_headcount_male')->nullable();
            $table->unsignedSmallInteger('org_headcount_female')->nullable();

            // Timestamp of the session itself (not created_at — the actual field time)
            $table->dateTime('session_datetime');

            // Personnel — ግንዛቤ ፈጣሪው ባለሞያ
            $table->foreignId('created_by')->constrained('users'); // Field Officer (Paramilitary)

            // ── Approval Workflow ──
            $table->enum('status', [
                'draft',      // saved but not submitted
                'submitted',  // waiting for Woreda Coordinator review
                'approved',   // signed off (የረጋገጠው ሀላፊ ስም)
                'rejected',   // sent back for correction
            ])->default('draft');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_note')->nullable(); // Coordinator's reason when rejecting

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awareness_engagements');
    }
};
