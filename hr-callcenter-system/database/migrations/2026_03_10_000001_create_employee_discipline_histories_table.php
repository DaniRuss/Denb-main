<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_discipline_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            $table->date('discipline_date');
            $table->string('discipline_type', 50);
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->string('status', 20)->default('active');

            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'discipline_date']);
            $table->index(['discipline_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_discipline_histories');
    }
};
