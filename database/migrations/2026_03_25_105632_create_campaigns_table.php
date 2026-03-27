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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_code')->unique(); // e.g. CAMP-20260325-001
            $table->string('name_am');                 // Amharic title
            $table->string('name_en');                 // English title
            $table->text('description_am')->nullable();
            $table->text('description_en')->nullable();
            $table->enum('category', [
                'house_to_house',   // ቀት ለቀት - One-on-one home visits
                'coffee_ceremony',  // ቡና ጠጹ - Coffee ceremony group sessions
                'organization',     // በአደረጃዘት - Community/Organization campaigns
            ])->default('house_to_house');
            // Geography — FK to existing tables
            $table->foreignId('sub_city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('woreda_id')->nullable()->constrained()->nullOnDelete();
            // Targeting
            $table->date('start_date');
            $table->date('end_date');
            $table->text('target_audience')->nullable();
            $table->text('target_location')->nullable();    // free-text block/area
            // Status lifecycle
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            // Ownership
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
