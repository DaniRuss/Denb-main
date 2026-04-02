<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_shift_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_assignment_id')->constrained('shift_assignments')->cascadeOnDelete();
            $table->text('report_text')->nullable();
            $table->unsignedInteger('incident_count')->default(0);
            $table->unsignedInteger('penalty_count')->default(0);
            $table->dateTime('submitted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_shift_reports');
    }
};
