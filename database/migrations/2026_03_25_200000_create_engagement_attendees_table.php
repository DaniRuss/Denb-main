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
        Schema::create('engagement_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engagement_id')->constrained('awareness_engagements')->cascadeOnDelete();
            $table->string('name_am');
            $table->enum('gender', ['male', 'female']);
            $table->unsignedTinyInteger('age')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagement_attendees');
    }
};
