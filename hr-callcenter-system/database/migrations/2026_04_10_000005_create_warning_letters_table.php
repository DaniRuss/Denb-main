<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_letters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('violation_record_id')->constrained('violation_records')->restrictOnDelete();

            // Letter identification
            $table->string('reference_number', 80)->unique();   // ቁጥር
            $table->string('warning_type', 20);                 // three_day | twenty_four_hour
            $table->date('issued_date');

            // Deadline tracking
            $table->dateTime('deadline');                        // when violator must comply
            $table->boolean('complied')->default(false);
            $table->dateTime('complied_at')->nullable();

            // Legal reference
            $table->string('regulation_number')->nullable();    // ደንብ ቁጥር
            $table->string('article')->nullable();
            $table->string('sub_article')->nullable();

            // Delivery
            $table->string('delivery_method', 30)->default('in_person'); // in_person | posted
            $table->boolean('violator_accepted')->default(true);

            // Escalation
            $table->boolean('escalated_to_task_force')->default(false);
            $table->date('escalation_date')->nullable();

            // Officers who issued/posted the warning
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('issued_by_officer_2')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['violation_record_id', 'warning_type']);
            $table->index('warning_type');
            $table->index('deadline');
            $table->index('issued_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_letters');
    }
};
