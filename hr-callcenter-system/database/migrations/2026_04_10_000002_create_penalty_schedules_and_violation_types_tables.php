<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The 6 tariff categories (ቅጣት ሰንጠረዥ 1-6)
        Schema::create('penalty_schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('name_am');                          // e.g. "ቅጣት ሰንጠረ��� 1"
            $table->string('name_en')->nullable();              // e.g. "Penalty Schedule 1"
            $table->unsignedTinyInteger('level');                // 1-6
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('level');
        });

        // Individual violation types within each schedule
        Schema::create('violation_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('penalty_schedule_id')->constrained('penalty_schedules')->cascadeOnDelete();
            $table->string('code', 20)->nullable();             // reference number in the regulation
            $table->string('name_am');                          // violation name in Amharic
            $table->string('name_en')->nullable();              // violation name in English
            $table->text('description')->nullable();
            $table->string('regulation_reference')->nullable(); // e.g. "ደንብ 150/2015 አንቀጽ 12 ንዑስ 3"
            $table->decimal('fine_amount', 12, 2);              // penalty amount in birr
            $table->decimal('min_fine', 12, 2)->nullable();     // for range-based fines
            $table->decimal('max_fine', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
            $table->index('penalty_schedule_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_types');
        Schema::dropIfExists('penalty_schedules');
    }
};
