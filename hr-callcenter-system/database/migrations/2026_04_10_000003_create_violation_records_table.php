<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_records', function (Blueprint $table): void {
            $table->id();

            // Who violated
            $table->foreignId('violator_id')->constrained('violators')->restrictOnDelete();
            $table->foreignId('violation_type_id')->constrained('violation_types')->restrictOnDelete();

            // Where it happened
            $table->foreignId('sub_city_id')->nullable()->constrained('sub_cities')->nullOnDelete();
            $table->foreignId('woreda_id')->nullable()->constrained('woredas')->nullOnDelete();
            $table->string('block')->nullable();                  // ብሎክ
            $table->string('specific_location')->nullable();    // ልዩ ቦታ

            // When
            $table->date('violation_date');
            $table->time('violation_time')->nullable();

            // Legal reference
            $table->string('regulation_number')->nullable();    // e.g. "150/2015"
            $table->string('article')->nullable();              // አንቀጽ
            $table->string('sub_article')->nullable();          // ንዑስ አንቀጽ

            // Penalty details
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->unsignedInteger('repeat_offense_count')->default(0); // ድግግሞሽ

            // Action & status tracking
            $table->string('action_taken')->nullable();         // የተወሰደ እርምጃ
            $table->string('status', 30)->default('open');      // open, warning_issued, penalty_issued, payment_pending, paid, court_filed, closed
            $table->text('investigation_notes')->nullable();    // ምርመራ

            // Officers involved
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();       // detecting officer
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); // shift leader verification

            $table->timestamps();
            $table->softDeletes();

            $table->index(['violator_id', 'violation_date']);
            $table->index(['sub_city_id', 'woreda_id', 'violation_date']);
            $table->index('status');
            $table->index('violation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_records');
    }
};
